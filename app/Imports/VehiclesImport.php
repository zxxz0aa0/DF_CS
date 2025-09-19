<?php

namespace App\Imports;

use App\Models\Vehicle;
use App\Models\Company;
use App\Models\CompanyCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class VehiclesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    protected $failures = [];
    protected $successCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // 轉換民國年為西元年
                $data = $this->transformData($row->toArray());

                // 驗證並新增車輛
                $validator = Validator::make($data, $this->rules());

                if ($validator->fails()) {
                    $this->failures[] = new Failure(
                        $rows->search($row) + 2, // Excel row number (header + 0-based index)
                        'validation',
                        $validator->errors()->all()
                    );
                    continue;
                }

                $data['created_by'] = auth()->id();
                $data['updated_by'] = auth()->id();

                Vehicle::create($data);
                $this->successCount++;

            } catch (\Exception $e) {
                $this->failures[] = new Failure(
                    $rows->search($row) + 2,
                    'exception',
                    [$e->getMessage()]
                );
            }
        }
    }

    public function rules(): array
    {
        return [
            'company_category_id' => 'required|exists:company_categories,id',
            'company_id' => 'required|exists:companies,id',
            'license_number' => 'required|string|max:20|unique:vehicles,license_number|regex:/^[A-Z0-9\-]+$/',
            'replacement_license' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
            'owner_name' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:50',
            'manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'manufacture_month' => 'nullable|integer|min:1|max:12',
            'vehicle_form' => 'nullable|string|max:50',
            'engine_displacement' => 'nullable|numeric|min:0|max:99999.99',
            'fuel_type' => 'nullable|string|max:20',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_style' => 'nullable|string|max:100',
            'engine_number' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'passenger_capacity' => 'nullable|integer|min:0|max:255',
            'vehicle_color' => 'nullable|string|max:30',
            'license_issue_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'license_issue_month' => 'nullable|integer|min:1|max:12',
            'license_issue_day' => 'nullable|integer|min:1|max:31',
            'inspection_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'inspection_month' => 'nullable|integer|min:1|max:12',
            'inspection_day' => 'nullable|integer|min:1|max:31',
            'registration_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'registration_month' => 'nullable|integer|min:1|max:12',
            'registration_day' => 'nullable|integer|min:1|max:31',
            'property_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'vehicle_status' => 'nullable|in:active,inactive',
        ];
    }

    private function transformData(array $row): array
    {
        // 查找公司類別ID
        $companyCategoryId = null;
        if (!empty($row['公司類別'])) {
            $category = CompanyCategory::where('name', $row['公司類別'])->first();
            $companyCategoryId = $category?->id;
        }

        // 查找公司ID
        $companyId = null;
        if (!empty($row['公司名稱'])) {
            $company = Company::where('name', $row['公司名稱'])->first();
            $companyId = $company?->id;
        }

        // 轉換民國年為西元年
        $licenseIssueYear = !empty($row['發照年民國']) ? ((int)$row['發照年民國'] + 1911) : null;
        $inspectionYear = !empty($row['檢驗年民國']) ? ((int)$row['檢驗年民國'] + 1911) : null;
        $registrationYear = !empty($row['入籍年民國']) ? ((int)$row['入籍年民國'] + 1911) : null;

        // 狀態轉換
        $vehicleStatus = 'active';
        if (!empty($row['狀態'])) {
            $vehicleStatus = ($row['狀態'] === '退籍') ? 'inactive' : 'active';
        }

        return [
            'company_category_id' => $companyCategoryId,
            'company_id' => $companyId,
            'license_number' => $row['車牌號碼'] ?? null,
            'replacement_license' => $row['替補車號'] ?? null,
            'vehicle_type' => $row['車輛類型'] ?? null,
            'owner_name' => $row['車主名稱'] ?? null,
            'address' => $row['地址'] ?? null,
            'brand' => $row['車輛廠牌'] ?? null,
            'manufacture_year' => !empty($row['出廠年']) ? (int)$row['出廠年'] : null,
            'manufacture_month' => !empty($row['出廠月']) ? (int)$row['出廠月'] : null,
            'vehicle_form' => $row['車輛形式'] ?? null,
            'engine_displacement' => !empty($row['排氣量']) ? (float)$row['排氣量'] : null,
            'fuel_type' => $row['燃料種類'] ?? null,
            'vehicle_model' => $row['車輛款式'] ?? null,
            'vehicle_style' => $row['車輛樣式'] ?? null,
            'engine_number' => $row['引擎號碼'] ?? null,
            'chassis_number' => $row['車身號碼'] ?? null,
            'passenger_capacity' => !empty($row['載運人數']) ? (int)$row['載運人數'] : null,
            'vehicle_color' => $row['車輛顏色'] ?? null,
            'license_issue_year' => $licenseIssueYear,
            'license_issue_month' => !empty($row['發照月']) ? (int)$row['發照月'] : null,
            'license_issue_day' => !empty($row['發照日']) ? (int)$row['發照日'] : null,
            'inspection_year' => $inspectionYear,
            'inspection_month' => !empty($row['檢驗月']) ? (int)$row['檢驗月'] : null,
            'inspection_day' => !empty($row['檢驗日']) ? (int)$row['檢驗日'] : null,
            'registration_year' => $registrationYear,
            'registration_month' => !empty($row['入籍月']) ? (int)$row['入籍月'] : null,
            'registration_day' => !empty($row['入籍日']) ? (int)$row['入籍日'] : null,
            'property_type' => $row['產權類別'] ?? null,
            'notes' => $row['備註'] ?? null,
            'vehicle_status' => $vehicleStatus,
        ];
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
}