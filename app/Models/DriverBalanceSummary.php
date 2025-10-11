<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DriverBalanceSummary extends Model
{
    protected $table = 'driver_balance_summary';

    protected $primaryKey = 'driver_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'driver_id',
        'driver_name',
        'driver_id_number',
        'total_debit',
        'total_credit',
        'balance',
        'last_transaction_date',
        'transaction_count',
        'updated_at',
    ];

    protected $casts = [
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'balance' => 'decimal:2',
        'transaction_count' => 'integer',
        'last_transaction_date' => 'date',
        'updated_at' => 'datetime',
    ];

    /**
     * 關聯到駕駛
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * 更新指定駕駛的餘額彙總
     */
    public static function updateBalance(int $driverId): void
    {
        $driver = Driver::find($driverId);
        if (! $driver) {
            // 駕駛不存在，刪除彙總記錄
            static::where('driver_id', $driverId)->delete();

            return;
        }

        $summary = DB::table('accounting_records')
            ->where('driver_id', $driverId)
            ->whereNull('deleted_at')
            ->selectRaw('
                COALESCE(SUM(debit_amount), 0) as total_debit,
                COALESCE(SUM(credit_amount), 0) as total_credit,
                MAX(transaction_date) as last_transaction_date,
                COUNT(*) as transaction_count
            ')
            ->first();

        $balance = $summary->total_debit - $summary->total_credit;

        static::updateOrCreate(
            ['driver_id' => $driverId],
            [
                'driver_name' => $driver->name,
                'driver_id_number' => $driver->id_number,
                'total_debit' => $summary->total_debit,
                'total_credit' => $summary->total_credit,
                'balance' => $balance,
                'last_transaction_date' => $summary->last_transaction_date,
                'transaction_count' => $summary->transaction_count,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * 重建所有駕駛的彙總（用於初始化或修復）
     */
    public static function rebuildAll(): void
    {
        DB::transaction(function () {
            // 清空現有彙總
            static::query()->delete();

            // 重新計算所有駕駛
            $driverIds = Driver::whereNull('deleted_at')->pluck('id');

            foreach ($driverIds as $driverId) {
                static::updateBalance($driverId);
            }
        });
    }

    /**
     * Scope: 只查詢有欠款的駕駛
     */
    public function scopeDebtors($query)
    {
        return $query->where('balance', '<', 0);
    }

    /**
     * Scope: 搜尋駕駛
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('driver_name', 'like', "%{$term}%")
                ->orWhere('driver_id_number', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: 依公司種類篩選
     */
    public function scopeByCompanyCategory($query, ?int $companyCategoryId)
    {
        if (! $companyCategoryId) {
            return $query;
        }

        return $query->whereHas('driver', function ($q) use ($companyCategoryId) {
            $q->where('company_category_id', $companyCategoryId);
        });
    }
}
