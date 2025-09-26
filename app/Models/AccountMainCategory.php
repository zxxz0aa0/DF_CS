<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountMainCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'category_name',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 關聯到子分類
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(AccountSubCategory::class, 'main_category_id');
    }

    /**
     * 關聯到所有科目明細（透過子分類）
     */
    public function accountDetails(): HasMany
    {
        return $this->hasManyThrough(
            AccountDetail::class,
            AccountSubCategory::class,
            'main_category_id',
            'sub_category_id'
        );
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
     * 取得啟用的總類
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
        return $query->orderBy('sort_order')->orderBy('category_code');
    }

    /**
     * 取得子分類數量
     */
    public function getSubCategoriesCountAttribute()
    {
        return $this->subCategories()->count();
    }
}