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
        $permissions = [
            'assignment.view' => '查看駕駛車輛綁定',
            'assignment.create' => '新增駕駛車輛綁定',
            'assignment.edit' => '編輯駕駛車輛綁定',
            'assignment.delete' => '刪除駕駛車輛綁定',
            'assignment.batch' => '批量綁定操作',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }
    }
}
