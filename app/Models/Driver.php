<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_category_id',
        'recurring_cost_id',
        'name',
        'id_number',
        'birthday',
        'contact_address',
        'residence_address',
        'home_phone',
        'mobile_phone1',
        'mobile_phone2',
        'emergency_contact',
        'emergency_phone',
        'registration_date',
        'deregistration_date',
        'fleet_join_date',
        'fleet_leave_date',
        'license_expire_date',
        'professional_license_expire_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'birthday' => 'date',
        'registration_date' => 'date',
        'deregistration_date' => 'date',
        'fleet_join_date' => 'date',
        'fleet_leave_date' => 'date',
        'license_expire_date' => 'date',
        'professional_license_expire_date' => 'date',
    ];

    protected $dates = ['deleted_at'];

    public function companyCategory(): BelongsTo
    {
        return $this->belongsTo(CompanyCategory::class);
    }

    public function getStatusTextAttribute(): string
    {
        return $this->status === 'open' ? '在籍中' : '已退籍';
    }

    public function getLicenseDaysRemainingAttribute(): ?int
    {
        if (! $this->license_expire_date) {
            return null;
        }

        return now()->diffInDays($this->license_expire_date, false);
    }

    public function getProfessionalLicenseDaysRemainingAttribute(): ?int
    {
        if (! $this->professional_license_expire_date) {
            return null;
        }

        return now()->diffInDays($this->professional_license_expire_date, false);
    }

    public function isLicenseExpiringSoon(int $days = 30): bool
    {
        $remaining = $this->license_days_remaining;

        return $remaining !== null && $remaining <= $days && $remaining >= 0;
    }

    public function isProfessionalLicenseExpiringSoon(int $days = 30): bool
    {
        $remaining = $this->professional_license_days_remaining;

        return $remaining !== null && $remaining <= $days && $remaining >= 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'close');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('id_number', 'like', "%{$term}%")
                ->orWhere('mobile_phone1', 'like', "%{$term}%")
                ->orWhere('mobile_phone2', 'like', "%{$term}%");
        });
    }

    public function scopeExpiringLicenses($query, int $days = 30)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereBetween('license_expire_date', [now(), now()->addDays($days)])
                ->orWhereBetween('professional_license_expire_date', [now(), now()->addDays($days)]);
        });
    }
}
