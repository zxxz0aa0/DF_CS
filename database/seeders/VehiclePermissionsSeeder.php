<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VehiclePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // 建立車輛管理相關權限
        $permissions = [
            'view vehicles' => '查看車輛',
            'create vehicles' => '新增車輛',
            'edit vehicles' => '編輯車輛',
            'delete vehicles' => '刪除車輛',
            'manage vehicle status' => '管理車輛狀態',
            'export vehicles' => '匯出車輛資料',
            'import vehicles' => '匯入車輛資料',
            'view expiring inspections' => '查看即將到期檢驗',
            'manage vehicle configs' => '管理車輛配置',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // 為 admin 角色分配所有車輛權限
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        // 建立車輛管理員角色 (如果不存在)
        $vehicleManagerRole = Role::firstOrCreate(
            ['name' => 'vehicle_manager'],
            ['guard_name' => 'web']
        );

        // 為車輛管理員分配大部分權限 (除了刪除和配置管理)
        $vehicleManagerPermissions = [
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'manage vehicle status',
            'export vehicles',
            'import vehicles',
            'view expiring inspections',
        ];

        $vehicleManagerRole->givePermissionTo($vehicleManagerPermissions);

        // 建立車輛檢視員角色 (如果不存在)
        $vehicleViewerRole = Role::firstOrCreate(
            ['name' => 'vehicle_viewer'],
            ['guard_name' => 'web']
        );

        // 為車輛檢視員分配查看相關權限
        $vehicleViewerPermissions = [
            'view vehicles',
            'view expiring inspections',
        ];

        $vehicleViewerRole->givePermissionTo($vehicleViewerPermissions);

        $this->command->info('車輛管理權限已建立完成');
        $this->command->info('- admin: 擁有所有車輛權限');
        $this->command->info('- vehicle_manager: 擁有車輛管理權限 (除刪除和配置)');
        $this->command->info('- vehicle_viewer: 僅擁有查看權限');
    }
}