<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLicenseAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false; // 只使用 created_at

    protected $fillable = [
        'vehicle_license_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime'
    ];

    const ACTIONS = [
        'created' => 'created',
        'updated' => 'updated',
        'deleted' => 'deleted',
        'transferred' => 'transferred',
        'revoked' => 'revoked'
    ];

    // 關聯設定
    public function vehicleLicense(): BelongsTo
    {
        return $this->belongsTo(VehicleLicense::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 範圍查詢
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByVehicleLicense($query, int $vehicleLicenseId)
    {
        return $query->where('vehicle_license_id', $vehicleLicenseId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // 存取器
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => '新增',
            'updated' => '修改',
            'deleted' => '刪除',
            'transferred' => '轉移',
            'revoked' => '繳銷',
            default => '未知操作'
        };
    }

    // 靜態方法：記錄審計日誌
    public static function log(string $action, VehicleLicense $vehicleLicense, ?array $oldValues = null, ?array $newValues = null): void
    {
        static::create([
            'vehicle_license_id' => $vehicleLicense->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
}
