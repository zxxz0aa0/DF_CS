<?php

namespace Database\Seeders;

use App\Models\CompanyCategory;
use Illuminate\Database\Seeder;

class CompanyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => '製造業',
                'description' => '從事產品製造、加工等相關業務的公司',
            ],
            [
                'name' => '服務業',
                'description' => '提供各種服務的公司，如餐飲、零售等',
            ],
            [
                'name' => '科技業',
                'description' => '從事軟體開發、資訊科技相關業務的公司',
            ],
            [
                'name' => '金融業',
                'description' => '銀行、保險、投資等金融相關業務公司',
            ],
            [
                'name' => '建築業',
                'description' => '從事建築、營造、工程相關業務的公司',
            ],
        ];

        foreach ($categories as $category) {
            CompanyCategory::create($category);
        }
    }
}
