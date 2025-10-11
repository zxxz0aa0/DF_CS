<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringCostItem extends Model
{
    protected $fillable = [
        'template_id',
        'account_detail_id',
        'amount',
        'note',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 關聯：所屬組合
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(RecurringCostTemplate::class, 'template_id');
    }

    /**
     * 關聯：會計科目
     */
    public function accountDetail(): BelongsTo
    {
        return $this->belongsTo(AccountDetail::class);
    }
}
