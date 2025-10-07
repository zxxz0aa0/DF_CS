<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CollectionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立催帳管理權限
        $permission = Permission::firstOrCreate([
            'name' => 'view collections',
            'guard_name' => 'web',
        ]);

        // 將權限分配給 admin 角色
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        $this->command->info('催帳管理權限已建立並分配給 admin 角色');
    }
}
