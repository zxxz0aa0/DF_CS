<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 建立快速搜尋權限
        $quickSearchPermissions = [
            'view quick search' => '查看快速搜尋',
        ];

        // 建立經常性費用權限
        $recurringCostsPermissions = [
            'view recurring costs' => '查看經常性費用',
            'create recurring costs' => '新增經常性費用',
            'edit recurring costs' => '編輯經常性費用',
            'delete recurring costs' => '刪除經常性費用',
        ];

        // 新增快速搜尋權限
        foreach ($quickSearchPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // 新增經常性費用權限
        foreach ($recurringCostsPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // 將所有權限分配給 admin 角色
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = array_merge(
                array_keys($quickSearchPermissions),
                array_keys($recurringCostsPermissions)
            );
            $adminRole->givePermissionTo($allPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 移除快速搜尋權限
        Permission::where('name', 'view quick search')->delete();

        // 移除經常性費用權限
        $recurringCostsPermissions = [
            'view recurring costs',
            'create recurring costs',
            'edit recurring costs',
            'delete recurring costs',
        ];

        foreach ($recurringCostsPermissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
};
