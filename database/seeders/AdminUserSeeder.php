<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 確保 admin 角色存在
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->command->error('Admin 角色不存在，請先執行 RolesAndPermissionsSeeder');
            return;
        }

        // 尋找系統管理員職務
        $adminPosition = Position::where('code', 'SYS_ADMIN')->first();
        if (!$adminPosition) {
            $this->command->error('系統管理員職務不存在，請先執行 PositionsAndPermissionsSeeder');
            return;
        }

        // 檢查是否已存在管理員帳號
        if (User::where('username', 'admin')->exists()) {
            $this->command->info('管理員帳號已存在，跳過建立');
            return;
        }

        // 建立預設管理員使用者
        $admin = User::create([
            'name' => '系統管理員',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'id_number' => 'A123456789',
            'birth_date' => '1980-01-01',
            'gender' => 'male',
            'mobile_phone' => '0912345678',
            'home_phone' => null,
            'address' => '台北市信義區市府路1號',
            'department' => 'IT部門',
            'position' => '系統管理員',
            'position_id' => $adminPosition->id, // 重要：分配系統管理員職務
            'emergency_contact' => '緊急聯絡人',
            'emergency_phone' => '0987654321',
        ]);

        // 分配 admin 角色
        $admin->assignRole($adminRole);

        // 建立測試使用者 (選用)
        $userRole = Role::where('name', 'user')->first();
        $driverPosition = Position::where('code', 'DRIVER')->first();

        if ($userRole && $driverPosition && !User::where('username', 'user')->exists()) {
            $user = User::create([
                'name' => '測試駕駛員',
                'username' => 'user',
                'email' => 'user@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'id_number' => 'B987654321',
                'birth_date' => '1990-05-15',
                'gender' => 'female',
                'mobile_phone' => '0987654321',
                'home_phone' => null,
                'address' => '台北市大安區忠孝東路100號',
                'department' => '營運部門',
                'position' => '駕駛員',
                'position_id' => $driverPosition->id,
                'emergency_contact' => '家屬聯絡人',
                'emergency_phone' => '0912345678',
            ]);

            $user->assignRole($userRole);
        }

        $this->command->info('===============================');
        $this->command->info('管理員帳號建立完成！');
        $this->command->info('===============================');
        $this->command->info('管理員登入資訊：');
        $this->command->info('  帳號: admin');
        $this->command->info('  密碼: password123');
        $this->command->info('  職務: ' . $adminPosition->name);
        $this->command->info('  權限數量: ' . $adminPosition->permissions()->count());
        $this->command->info('===============================');

        if (isset($user)) {
            $this->command->info('測試使用者登入資訊：');
            $this->command->info('  帳號: user');
            $this->command->info('  密碼: password123');
            $this->command->info('===============================');
        }
    }
}