<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'county',
        'license_number',
        'holder_name',
        'license_year',
        'license_month',
        'previous_license_number',
        'previous_holder_name',
        'previous_license_year',
        'previous_license_month',
        'notes',
        'replacement_date',
        'revocation_date',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'replacement_date' => 'date',
        'revocation_date' => 'date',
        'license_year' => 'integer',
        'license_month' => 'integer',
        'previous_license_year' => 'integer',
        'previous_license_month' => 'integer',
    ];

    protected $attributes = [
        'status' => 'active'
    ];

    // 關聯設定
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
        return $this->hasMany(VehicleLicenseAuditLog::class);
    }

    // 範圍查詢
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // 存取器
    public function getLicenseDateAttribute(): ?string
    {
        if ($this->license_year && $this->license_month) {
            return $this->license_year . '-' . str_pad($this->license_month, 2, '0', STR_PAD_LEFT);
        }
        return null;
    }

    public function getPreviousLicenseDateAttribute(): ?string
    {
        if ($this->previous_license_year && $this->previous_license_month) {
            return $this->previous_license_year . '-' . str_pad($this->previous_license_month, 2, '0', STR_PAD_LEFT);
        }
        return null;
    }

    // 狀態檢查方法
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }
}
