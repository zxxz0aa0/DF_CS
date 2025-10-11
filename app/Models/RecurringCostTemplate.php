<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringCostTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'total_amount',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_amount' => 'integer',
    ];

    /**
     * 關聯：費用項目
     */
    public function items(): HasMany
    {
        return $this->hasMany(RecurringCostItem::class, 'template_id')->orderBy('sort_order');
    }

    /**
     * 關聯：使用此組合的駕駛
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, 'recurring_cost_id');
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
     * 計算總金額
     */
    public function calculateTotalAmount(): int
    {
        return $this->items()->sum('amount');
    }

    /**
     * Scope: 啟用的組合
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: 搜尋
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
