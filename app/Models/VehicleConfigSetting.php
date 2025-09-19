<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleConfigSetting extends Model
{
    protected $fillable = [
        'company_category_id',
        'enable_replacement_license',
        'enable_owner_dropdown',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'enable_replacement_license' => 'boolean',
        'enable_owner_dropdown' => 'boolean',
    ];

    // 關聯設定
    public function companyCategory(): BelongsTo
    {
        return $this->belongsTo(CompanyCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 範圍查詢
    public function scopeByCompanyCategory($query, $categoryId)
    {
        return $query->where('company_category_id', $categoryId);
    }

    public function scopeWithReplacementLicense($query)
    {
        return $query->where('enable_replacement_license', true);
    }

    public function scopeWithOwnerDropdown($query)
    {
        return $query->where('enable_owner_dropdown', true);
    }

    // 靜態方法
    public static function getConfigForCategory($categoryId): ?self
    {
        return self::where('company_category_id', $categoryId)->first();
    }

    public static function isReplacementLicenseEnabled($categoryId): bool
    {
        $config = self::getConfigForCategory($categoryId);
        return $config ? $config->enable_replacement_license : false;
    }

    public static function isOwnerDropdownEnabled($categoryId): bool
    {
        $config = self::getConfigForCategory($categoryId);
        return $config ? $config->enable_owner_dropdown : false;
    }

    // 業務方法
    public function enableReplacementLicense(): bool
    {
        return $this->update(['enable_replacement_license' => true]);
    }

    public function disableReplacementLicense(): bool
    {
        return $this->update(['enable_replacement_license' => false]);
    }

    public function enableOwnerDropdown(): bool
    {
        return $this->update(['enable_owner_dropdown' => true]);
    }

    public function disableOwnerDropdown(): bool
    {
        return $this->update(['enable_owner_dropdown' => false]);
    }
}