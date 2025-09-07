<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'view admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 建立角色並分配權限

        // 使用者角色
        $userRole = Role::create(['name' => 'user']);

        // 管理員角色
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 建立預設管理員使用者
        $admin = User::create([
            'name' => '管理員',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        $admin->assignRole($adminRole);

        // 建立測試使用者
        $user = User::create([
            'name' => '測試使用者',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        $user->assignRole($userRole);

        $this->command->info('已建立角色、權限和預設使用者');
        $this->command->info('管理員登入：admin@example.com / password123');
        $this->command->info('使用者登入：user@example.com / password123');
    }
}
