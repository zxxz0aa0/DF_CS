<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PositionsAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立新的權限
        $permissions = [
            // 駕駛管理權限
            'view drivers',
            'create drivers',
            'edit drivers',
            'delete drivers',
            'export drivers',
            'import drivers',

            // 公司管理權限
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',

            // 職務管理權限
            'view positions',
            'create positions',
            'edit positions',
            'delete positions',

            // 報表權限
            'view reports',
            'create reports',
            'export reports',

            // 系統設定權限
            'manage system settings',
            'view system logs',
            'backup database',
            'restore database',

            // 財務權限
            'view financial reports',
            'manage billing',
            'view statistics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 確保角色存在
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 建立 Admin 角色下的職務
        $adminPositions = [
            [
                'name' => '系統管理員',
                'code' => 'SYS_ADMIN',
                'description' => '負責系統維護、使用者管理、權限配置等工作',
                'permissions' => [
                    'view admin dashboard',
                    'view users', 'create users', 'edit users', 'delete users',
                    'manage roles',
                    'view positions', 'create positions', 'edit positions', 'delete positions',
                    'manage system settings', 'view system logs', 'backup database', 'restore database',
                ],
            ],
            [
                'name' => '行政人員',
                'code' => 'ADMIN_STAFF',
                'description' => '負責日常行政事務、文件管理、基本資料維護',
                'permissions' => [
                    'view admin dashboard',
                    'view users',
                    'view drivers', 'create drivers', 'edit drivers',
                    'view companies', 'create companies', 'edit companies',
                    'view reports',
                    'view expense payments',
                ],
            ],
            [
                'name' => '會計人員',
                'code' => 'ACCOUNTANT',
                'description' => '負責財務管理、帳務處理、統計報表',
                'permissions' => [
                    'view admin dashboard',
                    'view financial reports',
                    'manage billing',
                    'view statistics',
                    'view reports', 'create reports', 'export reports',
                    'view drivers', 'export drivers',
                    'view expense payments', 'create expense payments', 'edit expense payments', 'delete expense payments', 'export expense payments', 'import expense payments',
                ],
            ],
            [
                'name' => '人事專員',
                'code' => 'HR_SPECIALIST',
                'description' => '負責人員招募、績效管理、使用者資料維護',
                'permissions' => [
                    'view admin dashboard',
                    'view users', 'create users', 'edit users',
                    'view positions',
                    'view drivers', 'create drivers', 'edit drivers', 'export drivers',
                    'view reports',
                ],
            ],
        ];

        foreach ($adminPositions as $positionData) {
            $position = Position::firstOrCreate([
                'code' => $positionData['code'],
            ], [
                'name' => $positionData['name'],
                'description' => $positionData['description'],
                'role_id' => $adminRole->id,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            // 分配權限給職務
            $permissionIds = [];
            foreach ($positionData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }

            $position->permissions()->sync($permissionIds);
        }

        // 建立 User 角色下的職務
        $userPositions = [
            [
                'name' => '駕駛員',
                'code' => 'DRIVER',
                'description' => '計程車駕駛員，可查看個人資料和基本資訊',
                'permissions' => [],
            ],
            [
                'name' => '調度員',
                'code' => 'DISPATCHER',
                'description' => '負責車輛調度、路線安排',
                'permissions' => [],
            ],
            [
                'name' => '客服人員',
                'code' => 'CUSTOMER_SERVICE',
                'description' => '負責客戶服務、投訴處理',
                'permissions' => [],
            ],
        ];

        foreach ($userPositions as $positionData) {
            $position = Position::firstOrCreate([
                'code' => $positionData['code'],
            ], [
                'name' => $positionData['name'],
                'description' => $positionData['description'],
                'role_id' => $userRole->id,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            // 分配權限給職務 (User 角色的職務目前沒有特殊權限)
            $permissionIds = [];
            foreach ($positionData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }

            if (! empty($permissionIds)) {
                $position->permissions()->sync($permissionIds);
            }
        }

        $this->command->info('職務和權限資料已成功建立！');
    }
}
