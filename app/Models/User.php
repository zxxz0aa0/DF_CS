<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'id_number',
        'birth_date',
        'home_phone',
        'mobile_phone',
        'emergency_contact',
        'emergency_phone',
        'department',
        'position',
        'gender',
        'address',
        'position_id',
    ];
    
    /**
     * 取得職務名稱（字符串）
     */
    public function getPositionNameAttribute()
    {
        if ($this->position_id) {
            if (!$this->relationLoaded('position')) {
                $this->load('position');
            }
            $positionModel = $this->getRelation('position');
            if ($positionModel) {
                return $positionModel->name;
            }
        }
        
        return $this->attributes['position'] ?? null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    // 已移除：身分證字號加密/解密的 Attribute 轉換

    /**
     * 使用者的職務
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * 檢查使用者是否有特定職務權限
     */
    public function hasPositionPermission(string $permission): bool
    {
        // 確保載入 position 關聯
        if ($this->position_id && !$this->relationLoaded('position')) {
            $this->load('position');
        }
        
        $positionModel = $this->getRelation('position');
        if (!$positionModel) {
            return false;
        }

        return $positionModel->hasPermission($permission);
    }

    /**
     * 檢查使用者是否有職務權限（職務優先架構）
     */
    public function hasAnyPermission(string $permission): bool
    {
        // 只檢查職務權限，不再檢查角色權限
        return $this->hasPositionPermission($permission);
    }

    /**
     * 獲取使用者所有權限（僅來自職務）
     */
    public function getAllPermissions()
    {
        // 確保載入 position 關聯及其權限
        if ($this->position_id && !$this->relationLoaded('position')) {
            $this->load('position.permissions');
        }
        
        $positionModel = $this->getRelation('position');
        return $positionModel?->permissions ?? collect([]);
    }

    /**
     * 檢查是否有指定權限（覆蓋 Spatie 的預設方法）
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // 優先使用職務權限檢查
        if ($this->hasPositionPermission($permission)) {
            return true;
        }

        // 如果沒有職務或職務沒有權限，則檢查角色權限（向後相容）
        return parent::hasPermissionTo($permission, $guardName);
    }

    /**
     * 根據帳號查找使用者（支援登入）
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }
}
