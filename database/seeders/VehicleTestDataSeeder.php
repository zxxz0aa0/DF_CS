<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyCategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleConfigSetting;
use Illuminate\Database\Seeder;

class VehicleTestDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // 確保有管理員用戶
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => '系統管理員',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]);
            $admin->assignRole('admin');
        }

        // 建立測試公司類別
        $category1 = CompanyCategory::firstOrCreate([
            'name' => '運輸公司',
        ]);

        $category2 = CompanyCategory::firstOrCreate([
            'name' => '物流公司',
        ]);

        // 建立測試公司
        $company1 = Company::firstOrCreate([
            'name' => '台北運輸股份有限公司',
            'category_id' => $category1->id,
        ]);

        $company2 = Company::firstOrCreate([
            'name' => '高雄物流有限公司',
            'category_id' => $category2->id,
        ]);

        // 建立車輛配置設定
        VehicleConfigSetting::updateOrCreate([
            'company_category_id' => $category1->id,
        ], [
            'enable_replacement_license' => true,
            'enable_owner_dropdown' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        VehicleConfigSetting::updateOrCreate([
            'company_category_id' => $category2->id,
        ], [
            'enable_replacement_license' => false,
            'enable_owner_dropdown' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // 建立測試車輛資料
        $vehicles = [
            [
                'company_category_id' => $category1->id,
                'company_id' => $company1->id,
                'license_number' => 'ABC-1234',
                'vehicle_type' => '小客車',
                'owner_name' => '台北運輸股份有限公司',
                'brand' => 'Toyota',
                'manufacture_year' => 2020,
                'manufacture_month' => 5,
                'fuel_type' => '汽油',
                'inspection_year' => 2024,
                'inspection_month' => 12,
                'inspection_day' => 15,
                'registration_year' => 2020,
                'registration_month' => 6,
                'registration_day' => 1,
                'vehicle_status' => 'active',
                'notes' => '公司主要營運車輛',
            ],
            [
                'company_category_id' => $category1->id,
                'company_id' => $company1->id,
                'license_number' => 'DEF-5678',
                'vehicle_type' => '小貨車',
                'owner_name' => '台北運輸股份有限公司',
                'brand' => 'Nissan',
                'manufacture_year' => 2019,
                'manufacture_month' => 8,
                'fuel_type' => '柴油',
                'inspection_year' => 2024,
                'inspection_month' => 10,
                'inspection_day' => 20,
                'registration_year' => 2019,
                'registration_month' => 9,
                'registration_day' => 15,
                'vehicle_status' => 'active',
            ],
            [
                'company_category_id' => $category2->id,
                'company_id' => $company2->id,
                'license_number' => 'GHI-9012',
                'vehicle_type' => '大貨車',
                'owner_name' => '高雄物流有限公司',
                'brand' => 'Mercedes-Benz',
                'manufacture_year' => 2018,
                'manufacture_month' => 3,
                'fuel_type' => '柴油',
                'inspection_year' => 2025,
                'inspection_month' => 3,
                'inspection_day' => 10,
                'registration_year' => 2018,
                'registration_month' => 4,
                'registration_day' => 1,
                'vehicle_status' => 'active',
            ],
            [
                'company_category_id' => $category2->id,
                'company_id' => $company2->id,
                'license_number' => 'JKL-3456',
                'vehicle_type' => '小客車',
                'owner_name' => '高雄物流有限公司',
                'brand' => 'Honda',
                'manufacture_year' => 2021,
                'manufacture_month' => 1,
                'fuel_type' => '油電混合',
                'inspection_year' => 2024,
                'inspection_month' => 9,
                'inspection_day' => 25,
                'registration_year' => 2021,
                'registration_month' => 2,
                'registration_day' => 10,
                'vehicle_status' => 'inactive',
                'deregistration_year' => 2024,
                'deregistration_month' => 8,
                'deregistration_day' => 15,
                'notes' => '因故障退籍的車輛',
            ],
            [
                'company_category_id' => $category1->id,
                'company_id' => $company1->id,
                'license_number' => 'MNO-7890',
                'vehicle_type' => '機車',
                'owner_name' => '台北運輸股份有限公司',
                'brand' => 'Yamaha',
                'manufacture_year' => 2022,
                'manufacture_month' => 7,
                'fuel_type' => '汽油',
                'inspection_year' => 2024,
                'inspection_month' => 9,
                'inspection_day' => 30,
                'registration_year' => 2022,
                'registration_month' => 8,
                'registration_day' => 5,
                'vehicle_status' => 'active',
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            $vehicleData['created_by'] = $admin->id;
            $vehicleData['updated_by'] = $admin->id;

            Vehicle::create($vehicleData);
        }

        $this->command->info('車輛測試資料已建立完成');
        $this->command->info('- 建立了 ' . count($vehicles) . ' 筆車輛資料');
        $this->command->info('- 管理員帳號: admin@example.com');
        $this->command->info('- 管理員密碼: password');
    }
}