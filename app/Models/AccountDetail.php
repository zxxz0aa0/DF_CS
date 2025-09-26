<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_category_id',
        'sub_category_id',
        'account_code',
        'account_name',
        'account_name_en',
        'description',
        'account_type',
        'debit_credit',
        'is_summary',
        'parent_id',
        'level',
        'sort_order',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_summary' => 'boolean',
        'sort_order' => 'integer',
        'level' => 'integer',
        'main_category_id' => 'integer',
        'sub_category_id' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * 關聯到總類
     */
    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(AccountMainCategory::class, 'main_category_id');
    }

    /**
     * 關聯到子分類
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(AccountSubCategory::class, 'sub_category_id');
    }

    /**
     * 關聯到上級科目
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountDetail::class, 'parent_id');
    }

    /**
     * 關聯到下級科目
     */
    public function children(): HasMany
    {
        return $this->hasMany(AccountDetail::class, 'parent_id');
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
     * 取得啟用的科目
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
        return $query->orderBy('sort_order')->orderBy('account_code');
    }

    /**
     * 根據總類篩選
     */
    public function scopeByMainCategory($query, $mainCategoryId)
    {
        return $query->where('main_category_id', $mainCategoryId);
    }

    /**
     * 根據子分類篩選
     */
    public function scopeBySubCategory($query, $subCategoryId)
    {
        return $query->where('sub_category_id', $subCategoryId);
    }

    /**
     * 根據科目性質篩選
     */
    public function scopeByAccountType($query, $accountType)
    {
        return $query->where('account_type', $accountType);
    }

    /**
     * 搜尋科目編號或名稱
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('account_code', 'like', "%{$search}%")
              ->orWhere('account_name', 'like', "%{$search}%")
              ->orWhere('account_name_en', 'like', "%{$search}%");
        });
    }

    /**
     * 取得科目性質中文名稱
     */
    public function getAccountTypeNameAttribute()
    {
        $types = [
            'asset' => '資產',
            'liability' => '負債',
            'equity' => '權益',
            'revenue' => '收入',
            'expense' => '費用'
        ];

        return $types[$this->account_type] ?? $this->account_type;
    }

    /**
     * 取得借貸性質中文名稱
     */
    public function getDebitCreditNameAttribute()
    {
        return $this->debit_credit === 'debit' ? '借方' : '貸方';
    }

    /**
     * 檢查是否有子科目
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * 取得科目階層路徑
     */
    public function getFullPathAttribute()
    {
        $path = collect([$this->account_name]);

        $parent = $this->parent;
        while ($parent) {
            $path->prepend($parent->account_name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }
}