<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccountPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 創建會計科目相關權限
        $permissions = [
            // 會計科目基本權限
            'view accounts' => '檢視會計科目',
            'create accounts' => '建立會計科目',
            'edit accounts' => '編輯會計科目',
            'delete accounts' => '刪除會計科目',
            'manage accounts' => '完整管理會計科目',

            // 會計科目進階權限
            'import accounts' => '匯入會計科目',
            'export accounts' => '匯出會計科目',
            'view account audit logs' => '查看會計科目審計日誌',

            // 會計科目總類權限
            'manage account categories' => '管理會計科目分類',
        ];

        // 創建權限
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // 創建或取得管理員角色
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        // 創建會計主管角色
        $accountingManagerRole = Role::firstOrCreate([
            'name' => 'accounting_manager',
            'guard_name' => 'web'
        ]);

        // 創建會計人員角色
        $accountingStaffRole = Role::firstOrCreate([
            'name' => 'accounting_staff',
            'guard_name' => 'web'
        ]);

        // 創建出納人員角色
        $cashierRole = Role::firstOrCreate([
            'name' => 'cashier',
            'guard_name' => 'web'
        ]);

        // 系統管理員擁有所有權限
        $adminRole->givePermissionTo(Permission::where('name', 'like', '%account%')->get());

        // 會計主管權限
        $accountingManagerRole->givePermissionTo([
            'view accounts',
            'create accounts',
            'edit accounts',
            'delete accounts',
            'manage accounts',
            'import accounts',
            'export accounts',
            'view account audit logs',
            'manage account categories',
        ]);

        // 會計人員權限
        $accountingStaffRole->givePermissionTo([
            'view accounts',
            'create accounts',
            'edit accounts',
            'export accounts',
        ]);

        // 出納人員權限（只能檢視）
        $cashierRole->givePermissionTo([
            'view accounts',
        ]);

        $this->command->info('會計科目權限和角色已建立成功！');
        $this->command->info('角色列表：');
        $this->command->info('- admin: 系統管理員（所有權限）');
        $this->command->info('- accounting_manager: 會計主管（完整會計權限）');
        $this->command->info('- accounting_staff: 會計人員（基本會計權限）');
        $this->command->info('- cashier: 出納人員（只能檢視）');
    }
}