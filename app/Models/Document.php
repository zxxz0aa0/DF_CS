<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'document_category',
        'document_name',
        'document_number',
        'insurance_level',
        'insurance_fee',
        'start_date',
        'expiry_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'insurance_level' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'days_until_expiry',
        'status_text',
        'status_color',
        'category_text',
    ];

    // 關聯
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class)->orderBy('sort_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 存取器
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return (int) now()->diffInDays($this->expiry_date, false);
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'valid' => '有效',
            'expiring_soon' => '即將到期',
            'expired' => '已過期',
            default => '未知',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'valid' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            default => 'secondary',
        };
    }

    public function getCategoryTextAttribute(): string
    {
        return match($this->document_category) {
            'identity' => '身分證件',
            'insurance' => '保險證件',
            'vehicle' => '車輛證件',
            default => '其他',
        };
    }

    // 範圍查詢
    public function scopeByCategory($query, $category)
    {
        return $query->where('document_category', $category);
    }

    public function scopeExpiringSoon($query, $days = 60)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function scopeValid($query)
    {
        return $query->whereNull('expiry_date')
            ->orWhere('expiry_date', '>', now()->addDays(60));
    }

    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    // 業務方法
    public function updateStatus(): void
    {
        if (!$this->expiry_date) {
            $this->status = 'valid';
            return;
        }

        $daysUntilExpiry = $this->days_until_expiry;

        if ($daysUntilExpiry < 0) {
            $this->status = 'expired';
        } elseif ($daysUntilExpiry <= 60) {
            $this->status = 'expiring_soon';
        } else {
            $this->status = 'valid';
        }

        $this->save();
    }

    public function isExpiringSoon(): bool
    {
        return $this->status === 'expiring_soon';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    // 模型事件
    protected static function booted()
    {
        static::saving(function ($document) {
            // 自動計算狀態
            if ($document->expiry_date) {
                $daysUntilExpiry = now()->diffInDays($document->expiry_date, false);

                if ($daysUntilExpiry < 0) {
                    $document->status = 'expired';
                } elseif ($daysUntilExpiry <= 60) {
                    $document->status = 'expiring_soon';
                } else {
                    $document->status = 'valid';
                }
            }
        });
    }
}
