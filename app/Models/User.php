<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles {
        hasPermissionTo as spatieHasPermissionTo;
        hasAnyPermission as spatieHasAnyPermission;
    }

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
        'position', // 保持原有欄位名稱
        'gender',
        'address',
        'position_id',
    ];


    /**
     * 覆寫 getAttribute 方法來處理 position 屬性衝突
     */
    public function getAttribute($key)
    {
        if ($key === 'position' && func_num_args() === 1) {
            // 當直接存取 position 屬性時，返回關聯
            return $this->getRelationValue('position');
        }
        
        return parent::getAttribute($key);
    }

    /**
     * 取得職務名稱字串（舊的 position 欄位值）
     */
    public function getPositionTitleAttribute()
    {
        return $this->getAttributeFromArray('position');
    }

    /**
     * 取得職務名稱（字符串）
     */
    public function getPositionNameAttribute()
    {
        if ($this->position_id) {
            if (! $this->relationLoaded('position')) {
                $this->load('position');
            }
            $positionModel = $this->getRelation('position');
            if ($positionModel) {
                return $positionModel->name;
            }
        }

        return $this->getAttributeFromArray('position');
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
        if ($this->position_id && ! $this->relationLoaded('position')) {
            $this->load('position');
        }

        $positionModel = $this->getRelation('position');
        if (! $positionModel) {
            return false;
        }

        return $positionModel->hasPermission($permission);
    }

    /**
     * 檢查是否擁有任一給定權限（職務優先，回退 Spatie）
     */
    public function hasAnyPermission(...$permissions): bool
    {
        $flat = collect($permissions)->flatten()->all();
        foreach ($flat as $permission) {
            if ($this->hasPositionPermission($permission)) {
                return true;
            }
        }
        return $this->spatieHasAnyPermission(...$permissions);
    }

    /**
     * 獲取使用者所有權限（僅來自職務）
     */
    public function getAllPermissions()
    {
        // 確保載入 position 關聯及其權限
        if ($this->position_id && ! $this->relationLoaded('position')) {
            $this->load('position.permissions');
        }

        $positionModel = $this->getRelation('position');

        return $positionModel?->permissions ?? collect([]);
    }

    /**
     * 檢查是否有指定權限（職務優先，回退 Spatie）
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasPositionPermission($permission)) {
            return true;
        }
        return $this->spatieHasPermissionTo($permission, $guardName);
    }

    /**
     * 根據帳號查找使用者（支援登入）
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }
}
