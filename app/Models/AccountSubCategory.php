<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_category_id',
        'sub_category_code',
        'sub_category_name',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'main_category_id' => 'integer',
    ];

    /**
     * 關聯到總類
     */
    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(AccountMainCategory::class, 'main_category_id');
    }

    /**
     * 關聯到科目明細
     */
    public function accountDetails(): HasMany
    {
        return $this->hasMany(AccountDetail::class, 'sub_category_id');
    }

    /**
     * 建立者關聯
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 更新者關聯
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 取得啟用的子分類
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 按排序順序排列
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('sub_category_code');
    }

    /**
     * 根據總類篩選
     */
    public function scopeByMainCategory($query, $mainCategoryId)
    {
        return $query->where('main_category_id', $mainCategoryId);
    }

    /**
     * 取得科目明細數量
     */
    public function getAccountDetailsCountAttribute()
    {
        return $this->accountDetails()->count();
    }

    /**
     * 取得完整代碼（總類代碼 + 子分類代碼）
     */
    public function getFullCodeAttribute()
    {
        return $this->mainCategory->category_code . $this->sub_category_code;
    }
}