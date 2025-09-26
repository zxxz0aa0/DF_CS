<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'auditable_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * 關聯到操作使用者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 取得被操作的模型實例
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * 取得操作事件的中文名稱
     */
    public function getEventNameAttribute()
    {
        $events = [
            'created' => '新增',
            'updated' => '更新',
            'deleted' => '刪除',
        ];

        return $events[$this->event] ?? $this->event;
    }

    /**
     * 取得模型類別的中文名稱
     */
    public function getModelNameAttribute()
    {
        $models = [
            'App\Models\AccountMainCategory' => '會計科目總類',
            'App\Models\AccountSubCategory' => '會計科目子分類',
            'App\Models\AccountDetail' => '會計科目明細',
        ];

        return $models[$this->auditable_type] ?? $this->auditable_type;
    }

    /**
     * 按時間倒序排列
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * 根據使用者篩選
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 根據模型類型篩選
     */
    public function scopeByModel($query, $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    /**
     * 根據操作事件篩選
     */
    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * 記錄審計日誌的靜態方法
     */
    public static function log($model, $event, $oldValues = null, $newValues = null)
    {
        return self::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'created_at' => now(),
        ]);
    }
}