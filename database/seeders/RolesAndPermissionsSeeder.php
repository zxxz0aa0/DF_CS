<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 重置快取的角色和權限
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 建立權限
        $permissions = [
            // 使用者管理權限
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // 角色權限管理權限
            'manage roles',
            
            // 職務管理權限
            'view positions',
            'create positions',
            'edit positions',
            'delete positions',
            
            // 公司管理權限
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',
            'view company categories',
            'create company categories',
            'edit company categories',
            'delete company categories',
            
            // 駕駛管理權限
            'view drivers',
            'create drivers',
            'edit drivers',
            'delete drivers',
            'export drivers',
            'import drivers',
            'view expiring licenses',
            
            // 車輛牌照管理權限
            'view vehicle licenses',
            'create vehicle licenses',
            'edit vehicle licenses',
            'delete vehicle licenses',
            'revoke vehicle licenses',
            'transfer vehicle licenses',
            'import vehicle licenses',
            'export vehicle licenses',
            'view vehicle license audit logs',

            // 系統管理權限
            'view admin dashboard',

            // 支出款項管理
            'view expense payments',
            'create expense payments',
            'edit expense payments',
            'delete expense payments',
            'import expense payments',
            'export expense payments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 建立角色並分配權限

        // 使用者角色
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 管理員角色
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $this->command->info('已建立基礎角色和權限');
        $this->command->info('Admin 角色已分配所有權限');
        $this->command->info('User 角色無特殊權限（將透過職務獲得權限）');
    }
}
