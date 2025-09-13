<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,     // 建立基礎角色權限
            PositionsAndPermissionsSeeder::class, // 建立職務和詳細權限
            AdminUserSeeder::class,               // 建立完整管理員帳號
            CompanyCategorySeeder::class,
        ]);
    }
}
