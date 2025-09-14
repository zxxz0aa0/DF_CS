<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\VehicleLicense;
use App\Models\VehicleLicenseAuditLog;
use Illuminate\Database\Seeder;

class VehicleLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 確保有公司和使用者資料
        $companies = Company::all();
        $users = User::all();

        if ($companies->isEmpty() || $users->isEmpty()) {
            $this->command->warn('需要先執行公司和使用者的 Seeder');
            return;
        }

        $firstUser = $users->first();
        
        // 建立測試牌照資料
        $testLicenses = [
            [
                'company_id' => $companies->first()->id,
                'county' => '台北市',
                'license_number' => 'ABC-1234',
                'holder_name' => '王小明',
                'license_year' => 2024,
                'license_month' => 1,
                'notes' => '測試牌照資料',
                'status' => 'active',
                'created_by' => $firstUser->id,
                'updated_by' => $firstUser->id,
            ],
            [
                'company_id' => $companies->first()->id,
                'county' => '新北市',
                'license_number' => 'DEF-5678',
                'holder_name' => '李小華',
                'license_year' => 2024,
                'license_month' => 2,
                'previous_license_number' => 'OLD-1111',
                'previous_holder_name' => '張三',
                'previous_license_year' => 2023,
                'previous_license_month' => 12,
                'replacement_date' => '2024-02-15',
                'notes' => '替補牌照',
                'status' => 'active',
                'created_by' => $firstUser->id,
                'updated_by' => $firstUser->id,
            ],
            [
                'company_id' => $companies->first()->id,
                'county' => '台中市',
                'license_number' => 'GHI-9999',
                'holder_name' => '陳小美',
                'license_year' => 2023,
                'license_month' => 6,
                'revocation_date' => '2024-01-15',
                'notes' => '已繳銷牌照',
                'status' => 'revoked',
                'created_by' => $firstUser->id,
                'updated_by' => $firstUser->id,
            ]
        ];

        foreach ($testLicenses as $licenseData) {
            $license = VehicleLicense::create($licenseData);
            
            // 建立對應的審計記錄
            VehicleLicenseAuditLog::log('created', $license, null, $licenseData);
            
            // 如果是已繳銷的牌照，再建立一個繳銷記錄
            if ($license->status === 'revoked') {
                VehicleLicenseAuditLog::log('revoked', $license, ['status' => 'active'], ['status' => 'revoked', 'revocation_date' => $license->revocation_date]);
            }
        }

        // 如果有多個公司，為其他公司也建立一些資料
        if ($companies->count() > 1) {
            $secondCompany = $companies->skip(1)->first();
            
            VehicleLicense::create([
                'company_id' => $secondCompany->id,
                'county' => '高雄市',
                'license_number' => 'KHH-0001',
                'holder_name' => '林大明',
                'license_year' => 2024,
                'license_month' => 3,
                'notes' => '其他公司測試資料',
                'status' => 'active',
                'created_by' => $firstUser->id,
                'updated_by' => $firstUser->id,
            ]);
        }

        $this->command->info('車輛牌照測試資料建立完成');
        $this->command->info('建立了 ' . VehicleLicense::count() . ' 筆牌照資料');
        $this->command->info('建立了 ' . VehicleLicenseAuditLog::count() . ' 筆審計記錄');
    }
}
