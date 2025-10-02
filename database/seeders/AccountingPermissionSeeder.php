<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Position;

class AccountingPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立帳務管理權限
        $permissions = [
            'view accounting',        // 查看帳務記錄
            'create accounting',      // 新增帳務記錄
            'edit accounting',        // 編輯帳務記錄
            'delete accounting',      // 刪除帳務記錄
            'export accounting',      // 匯出帳務記錄
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 為系統管理員職務新增帳務管理權限
        $adminPosition = Position::where('code', 'SYS_ADMIN')->first();
        if ($adminPosition) {
            $adminPosition->givePermissionTo([
                'view accounting',
                'create accounting',
                'edit accounting',
                'delete accounting',
                'export accounting',
            ]);
            $this->command->info('已為系統管理員職務新增帳務管理權限');
        }

        // 為會計人員職務新增帳務管理權限（如果存在）
        $accountantPosition = Position::where('code', 'ACCOUNTANT')->first();
        if ($accountantPosition) {
            $accountantPosition->givePermissionTo([
                'view accounting',
                'create accounting',
                'edit accounting',
                'delete accounting',
                'export accounting',
            ]);
            $this->command->info('已為會計人員職務新增帳務管理權限');
        }

        $this->command->info('帳務管理權限建立完成');
    }
}
