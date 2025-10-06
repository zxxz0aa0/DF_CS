<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DocumentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 文件管理權限
        $permissions = [
            'view documents' => '查看文件',
            'create documents' => '新增文件',
            'edit documents' => '編輯文件',
            'delete documents' => '刪除文件',
            'download documents' => '下載文件',
        ];

        // 建立權限
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // 將權限分配給 admin 角色
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        $this->command->info('文件管理權限已建立並分配給 admin 角色');
    }
}
