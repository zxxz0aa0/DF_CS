<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    // 關聯
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 業務方法
    public static function logVehicleAction(
        int $vehicleId,
        string $action,
        string $description = null,
        array $oldValues = null,
        array $newValues = null,
        array $changes = null
    ): void {
        static::create([
            'vehicle_id' => $vehicleId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    // 取得動作的中文說明
    public function getActionTextAttribute(): string
    {
        return match ($this->action) {
            'create' => '新增車輛',
            'update' => '更新車輛',
            'delete' => '刪除車輛',
            'deregister' => '車輛退籍',
            'reactivate' => '車輛復籍',
            'import' => '匯入車輛',
            'restore' => '恢復車輛',
            default => $this->action,
        };
    }

    // 範圍查詢
    public function scopeByVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}