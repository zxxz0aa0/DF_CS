<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DriverVehicleAssignmentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 舊的權限名稱（使用點號分隔）
        $oldPermissions = [
            'assignment.view',
            'assignment.create',
            'assignment.edit',
            'assignment.delete',
            'assignment.batch',
        ];

        // 新的權限名稱（統一使用空格分隔）
        $newPermissions = [
            'view driver vehicle assignments',
            'create driver vehicle assignments',
            'edit driver vehicle assignments',
            'delete driver vehicle assignments',
            'batch driver vehicle assignments',
        ];

        // 刪除舊的權限
        foreach ($oldPermissions as $oldName) {
            $oldPerm = Permission::where('name', $oldName)->first();
            if ($oldPerm) {
                $oldPerm->delete();
            }
        }

        // 建立新的權限
        foreach ($newPermissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // 將新權限分配給 admin 角色
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($newPermissions);
        }

        $this->command->info('駕駛車輛綁定管理權限已更新為統一格式');
    }
}
