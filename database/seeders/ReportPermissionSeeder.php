<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Position;

class ReportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立報表管理權限
        $permissions = [
            'view reports',                    // 查看報表
            'create report configurations',    // 建立報表組合
            'edit report configurations',      // 編輯報表組合
            'delete report configurations',    // 刪除報表組合
            'export reports',                  // 匯出報表
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 為系統管理員職務新增報表管理權限
        $adminPosition = Position::where('code', 'SYS_ADMIN')->first();
        if ($adminPosition) {
            $adminPosition->givePermissionTo([
                'view reports',
                'create report configurations',
                'edit report configurations',
                'delete report configurations',
                'export reports',
            ]);
            $this->command->info('已為系統管理員職務新增報表管理權限');
        }

        // 為會計人員職務新增報表管理權限（如果存在）
        $accountantPosition = Position::where('code', 'ACCOUNTANT')->first();
        if ($accountantPosition) {
            $accountantPosition->givePermissionTo([
                'view reports',
                'create report configurations',
                'edit report configurations',
                'delete report configurations',
                'export reports',
            ]);
            $this->command->info('已為會計人員職務新增報表管理權限');
        }

        $this->command->info('報表管理權限建立完成');
    }
}
