<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_category_id',
        'company_id',
        'license_number',
        'replacement_license',
        'vehicle_type',
        'owner_name',
        'address',
        'brand',
        'manufacture_year',
        'manufacture_month',
        'vehicle_form',
        'engine_displacement',
        'fuel_type',
        'vehicle_model',
        'vehicle_style',
        'engine_number',
        'chassis_number',
        'passenger_capacity',
        'vehicle_color',
        'license_issue_year',
        'license_issue_month',
        'license_issue_day',
        'inspection_year',
        'inspection_month',
        'inspection_day',
        'registration_year',
        'registration_month',
        'registration_day',
        'deregistration_year',
        'deregistration_month',
        'deregistration_day',
        'property_type',
        'notes',
        'vehicle_status',
        'created_by',
        'updated_by',
    ];

    protected $appends = [
        'status_text',
        'inspection_date',
        'registration_date',
        'deregistration_date',
        'license_issue_date',
        'inspection_days_remaining',
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'manufacture_month' => 'integer',
        'license_issue_year' => 'integer',
        'license_issue_month' => 'integer',
        'license_issue_day' => 'integer',
        'inspection_year' => 'integer',
        'inspection_month' => 'integer',
        'inspection_day' => 'integer',
        'registration_year' => 'integer',
        'registration_month' => 'integer',
        'registration_day' => 'integer',
        'deregistration_year' => 'integer',
        'deregistration_month' => 'integer',
        'deregistration_day' => 'integer',
        'passenger_capacity' => 'integer',
        'engine_displacement' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    // 關聯設定
    public function companyCategory(): BelongsTo
    {
        return $this->belongsTo(CompanyCategory::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(VehicleAuditLog::class);
    }

    // 存取器 (Accessors)
    public function getStatusTextAttribute(): string
    {
        return $this->vehicle_status === 'active' ? '在籍' : '退籍';
    }

    public function getInspectionDateAttribute(): ?string
    {
        if ($this->inspection_year && $this->inspection_month && $this->inspection_day) {
            return sprintf('%04d-%02d-%02d', $this->inspection_year, $this->inspection_month, $this->inspection_day);
        }
        return null;
    }

    public function getRegistrationDateAttribute(): ?string
    {
        if ($this->registration_year && $this->registration_month && $this->registration_day) {
            return sprintf('%04d-%02d-%02d', $this->registration_year, $this->registration_month, $this->registration_day);
        }
        return null;
    }

    public function getDeregistrationDateAttribute(): ?string
    {
        if ($this->deregistration_year && $this->deregistration_month && $this->deregistration_day) {
            return sprintf('%04d-%02d-%02d', $this->deregistration_year, $this->deregistration_month, $this->deregistration_day);
        }
        return null;
    }

    public function getLicenseIssueDateAttribute(): ?string
    {
        if ($this->license_issue_year && $this->license_issue_month && $this->license_issue_day) {
            return sprintf('%04d-%02d-%02d', $this->license_issue_year, $this->license_issue_month, $this->license_issue_day);
        }
        return null;
    }

    public function getInspectionDaysRemainingAttribute(): ?int
    {
        if (!$this->inspection_date) {
            return null;
        }

        return (int) now()->diffInDays($this->inspection_date, false);
    }

    // 範圍查詢 (Scopes)
    public function scopeActive($query)
    {
        return $query->where('vehicle_status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('vehicle_status', 'inactive');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('license_number', 'like', "%{$term}%")
                ->orWhere('owner_name', 'like', "%{$term}%")
                ->orWhere('chassis_number', 'like', "%{$term}%")
                ->orWhere('engine_number', 'like', "%{$term}%");
        });
    }

    public function scopeByCompanyCategory($query, $categoryId)
    {
        return $query->where('company_category_id', $categoryId);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeExpiringInspection($query, int $days = 30)
    {
        $futureDate = now()->addDays($days);

        return $query->whereNotNull('inspection_year')
            ->whereNotNull('inspection_month')
            ->whereNotNull('inspection_day')
            ->whereRaw("
                CONCAT(inspection_year, '-', LPAD(inspection_month, 2, '0'), '-', LPAD(inspection_day, 2, '0'))
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$days} DAY)
            ");
    }

    // 業務方法
    public function isActive(): bool
    {
        return $this->vehicle_status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->vehicle_status === 'inactive';
    }

    public function isInspectionExpiringSoon(int $days = 30): bool
    {
        $remaining = $this->inspection_days_remaining;
        return $remaining !== null && $remaining <= $days && $remaining >= 0;
    }

    public function deregister(): bool
    {
        $now = now();
        return $this->update([
            'vehicle_status' => 'inactive',
            'deregistration_year' => $now->year,
            'deregistration_month' => $now->month,
            'deregistration_day' => $now->day,
        ]);
    }

    public function reactivate(): bool
    {
        return $this->update([
            'vehicle_status' => 'active',
            'deregistration_year' => null,
            'deregistration_month' => null,
            'deregistration_day' => null,
        ]);
    }

    // 民國年轉換方法
    public static function convertRepublicToWestern($republicYear): int
    {
        return $republicYear + 1911;
    }

    public static function convertWesternToRepublic($westernYear): int
    {
        return $westernYear - 1911;
    }
}