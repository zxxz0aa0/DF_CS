<?php

namespace App\Imports;

use App\Models\ExpensePayment;
use App\Services\ExpensePaymentService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Validators\Failure;

class ExpensePaymentsImport implements ToCollection, SkipsOnFailure
{
    protected array $failures = [];
    protected int $successCount = 0;
    protected array $headers = [];

    public function __construct(private readonly ExpensePaymentService $expensePaymentService)
    {
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // 第一列是標題列
        $this->headers = array_map([$this, 'normalizeHeaderName'], $rows->first()->toArray());

        // 從第二列開始處理資料
        $dataRows = $rows->skip(1)->values(); // 重新索引
        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2; // 實際的 Excel 列號（標題是第1列，資料從第2列開始）

            $rowData = $this->mapRowToHeaders($row->toArray());
            $data = $this->transformRow($rowData);

            $validator = Validator::make($data, $this->rules());

            if ($validator->fails()) {
                $this->failures[] = new Failure($rowNumber, 'validation', $validator->errors()->all());
                continue;
            }

            // 檢查重複資料
            if ($this->isDuplicate($data)) {
                $this->failures[] = new Failure(
                    $rowNumber,
                    'duplicate',
                    ['此記錄已存在：隊員編號=' . ($data['member_code'] ?? '未提供') . '，紀錄日期=' . $data['record_date'] . '，紀錄時間=' . $data['record_time']]
                );
                continue;
            }

            try {
                $this->expensePaymentService->create($data);
                $this->successCount++;
            } catch (\Throwable $throwable) {
                $this->failures[] = new Failure($rowNumber, 'exception', [$throwable->getMessage()]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'record_date' => ['required', 'date'],
            'record_time' => ['required', 'date_format:H:i'],
            'member_code' => ['nullable', 'string', 'max:50'],
            'member_name' => ['required', 'string', 'max:100'],
            'vehicle_license_number' => ['nullable', 'string', 'max:20'],
            'item_name' => ['required', 'string', 'max:120'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'net_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * 將資料列對應到標題列
     */
    protected function mapRowToHeaders(array $rowValues): array
    {
        $mapped = [];
        foreach ($this->headers as $index => $header) {
            $header = $this->normalizeHeaderName($header);
            if ($header === '') {
                continue;
            }
            $canonical = $this->canonicalizeHeader($header);
            if ($canonical === '') {
                continue;
            }
            if (isset($rowValues[$index])) {
                $mapped[$canonical] = $rowValues[$index];
            }
        }
        return $mapped;
    }

    /**
     * 標題名稱清洗（去除前後空白）
     */
    protected function normalizeHeaderName($header): string
    {
        if (! is_string($header)) {
            return (string) $header;
        }
        // 移除 BOM、全形空白、零寬空白
        $header = str_replace(["\u{FEFF}", "\u{200B}", "\u{00A0}"], '', $header);
        return trim($header);
    }

    /**
     * 將標題映射為統一欄位鍵名
     */
    protected function canonicalizeHeader(string $header): string
    {
        $map = [
            '紀錄日期' => 'record_date',
            '交易日期' => 'record_date',
            'record_date' => 'record_date',
            '紀錄時間' => 'record_time',
            '時間' => 'record_time',
            'record_time' => 'record_time',
            '隊員編號' => 'member_code',
            'member_code' => 'member_code',
            '隊員姓名' => 'member_name',
            'member_name' => 'member_name',
            '車牌號碼' => 'vehicle_license_number',
            'vehicle_license_number' => 'vehicle_license_number',
            '款項名稱' => 'item_name',
            '款項' => 'item_name',
            'item_name' => 'item_name',
            '支付金額' => 'gross_amount',
            '支付金額(元)' => 'gross_amount',
            'gross_amount' => 'gross_amount',
            '應扣款' => 'deduction',
            'deduction' => 'deduction',
            '實付金額' => 'net_amount',
            'net_amount' => 'net_amount',
            '狀態' => 'status',
            'status' => 'status',
            '支付日期' => 'payment_date',
            'payment_date' => 'payment_date',
            '支付方式' => 'payment_method',
            'payment_method' => 'payment_method',
            '備註' => 'note',
            'note' => 'note',
        ];

        return $map[$header] ?? $header;
    }

    protected function transformRow(array $row): array
    {
        // 簡化的欄位取得（直接支援中文標題）
        $recordDate = $this->parseDate($row['紀錄日期'] ?? $row['record_date'] ?? null);
        $paymentDate = $this->parseDate($row['支付日期'] ?? $row['payment_date'] ?? null);
        $recordTime = $this->normalizeTime($row['紀錄時間'] ?? $row['record_time'] ?? null);
        // 將隊員編號轉為字串，避免數字格式被驗證擋下
        $memberCode = $row['隊員編號'] ?? $row['member_code'] ?? null;
        if (is_numeric($memberCode)) {
            $memberCode = (string) $memberCode;
        } elseif (is_string($memberCode)) {
            $memberCode = trim($memberCode);
        }

        $statusRaw = $row['狀態'] ?? $row['status'] ?? 'pending';
        $status = strtolower((string) $statusRaw);
        $status = in_array($status, ['paid', '已支付'], true) ? 'paid' : 'pending';

        $gross = (float) ($row['支付金額'] ?? $row['gross_amount'] ?? 0);
        $deduction = (float) ($row['應扣款'] ?? $row['deduction'] ?? 0);
        $net = $row['實付金額'] ?? $row['net_amount'] ?? ($gross - $deduction);

        if ($status === 'paid' && ! $paymentDate) {
            $paymentDate = $recordDate;
        }

        return [
            'record_date' => $recordDate,
            'record_time' => $recordTime,
            'member_code' => $memberCode,
            'member_name' => $row['隊員姓名'] ?? $row['member_name'] ?? null,
            'vehicle_license_number' => $row['車牌號碼'] ?? $row['vehicle_license_number'] ?? null,
            'item_name' => $row['款項名稱'] ?? $row['item_name'] ?? null,
            'gross_amount' => $gross,
            'deduction' => $deduction,
            'net_amount' => $net,
            'status' => $status,
            'payment_date' => $paymentDate,
            'payment_method' => $row['支付方式'] ?? $row['payment_method'] ?? null,
            'note' => $row['備註'] ?? $row['note'] ?? null,
        ];
    }

    protected function parseDate($value): ?string
    {
        if (! $value) {
            return null;
        }

        // 如果是 Excel 序列號（數字格式）
        if (is_numeric($value)) {
            try {
                // Excel 的日期起始點是 1900-01-01
                $unixTimestamp = ($value - 25569) * 86400;
                return Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        // 如果是字串格式
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    protected function normalizeTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // 如果是 Excel 時間小數格式（0.0 到 1.0 之間）
        if (is_numeric($value) && (float)$value >= 0 && (float)$value < 1) {
            $totalSeconds = round((float)$value * 86400);
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            return sprintf('%02d:%02d', (int)$hours, (int)$minutes);
        }

        // 如果是字串格式
        $stringValue = (string) $value;

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $stringValue, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $stringValue, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        try {
            return Carbon::parse($stringValue)->format('H:i');
        } catch (\Exception) {
            return null;
        }
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    protected function isDuplicate(array $data): bool
    {
        $query = ExpensePayment::where('record_date', $data['record_date'])
            ->where('record_time', $data['record_time']);

        // 使用 member_code 進行查詢（若存在的話）
        if (! empty($data['member_code'])) {
            $query->where('member_code', $data['member_code']);
        }

        return $query->exists();
    }
}
