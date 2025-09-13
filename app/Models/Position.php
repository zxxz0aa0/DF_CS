<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Position extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'role_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 所屬角色
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 此職務的使用者
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * 職務擁有的權限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'position_permissions');
    }

    /**
     * 檢查是否有特定權限
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * 分配權限給職務
     */
    public function givePermissionTo($permissions): self
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            $permissionModel = Permission::where('name', $permission)->first();
            if ($permissionModel) {
                $this->permissions()->syncWithoutDetaching($permissionModel->id);
            }
        }

        return $this;
    }

    /**
     * 撤銷職務權限
     */
    public function revokePermissionTo($permissions): self
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            $permissionModel = Permission::where('name', $permission)->first();
            if ($permissionModel) {
                $this->permissions()->detach($permissionModel->id);
            }
        }

        return $this;
    }

    /**
     * 同步職務權限
     */
    public function syncPermissions($permissions): self
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_numeric($permission)) {
                return $permission;
            }

            return Permission::where('name', $permission)->first()?->id;
        })->filter()->values()->toArray();

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * 只顯示啟用的職務
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 按角色篩選
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * 按排序顯示
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
