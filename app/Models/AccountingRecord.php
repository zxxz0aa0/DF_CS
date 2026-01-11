<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'account_detail_id',
        'transaction_date',
        'driver_name',
        'driver_id_number',
        'vehicle_license_number',
        'vehicle_fleet_number',
        'debit_amount',
        'credit_amount',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    /**
     * 關聯：駕駛
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    /**
     * 關聯：車輛
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    /**
     * 關聯：會計科目明細
     */
    public function accountDetail(): BelongsTo
    {
        return $this->belongsTo(AccountDetail::class);
    }

    /**
     * 關聯：建立者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 關聯：更新者
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 自動填入建立者與更新駕駛餘額彙總
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // 新增帳務記錄時更新彙總
        static::created(function ($record) {
            if ($record->driver_id) {
                DriverBalanceSummary::updateBalance($record->driver_id);
            }
        });

        // 更新帳務記錄時重新計算
        static::updated(function ($record) {
            // 更新當前駕駛的彙總
            if ($record->driver_id) {
                DriverBalanceSummary::updateBalance($record->driver_id);
            }

            // 如果 driver_id 改變，舊駕駛也要更新
            if ($record->isDirty('driver_id') && $record->getOriginal('driver_id')) {
                DriverBalanceSummary::updateBalance($record->getOriginal('driver_id'));
            }
        });

        // 刪除帳務記錄時更新（包含軟刪除）
        static::deleted(function ($record) {
            if ($record->driver_id) {
                DriverBalanceSummary::updateBalance($record->driver_id);
            }
        });

        // 恢復軟刪除的記錄時更新
        static::restored(function ($record) {
            if ($record->driver_id) {
                DriverBalanceSummary::updateBalance($record->driver_id);
            }
        });
    }

    /**
     * Scope: 依駕駛篩選
     */
    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope: 依車輛篩選
     */
    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Scope: 依日期範圍篩選
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope: 依關鍵字搜尋
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('driver_name', 'like', "%{$keyword}%")
              ->orWhere('driver_id_number', 'like', "%{$keyword}%")
              ->orWhere('vehicle_license_number', 'like', "%{$keyword}%")
              ->orWhere('note', 'like', "%{$keyword}%");
        });
    }
}
