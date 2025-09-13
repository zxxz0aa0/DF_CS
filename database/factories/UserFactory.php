<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('zh_TW');
        
        return [
            'name' => $faker->name(),
            'username' => $faker->unique()->userName(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'id_number' => $this->generateTaiwanId(),
            'birth_date' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => $faker->randomElement(['male', 'female']),
            'mobile_phone' => '09' . $faker->numerify('########'),
            'home_phone' => null,
            'address' => $faker->address(),
            'department' => $faker->randomElement(['IT部門', '人事部門', '財務部門', '行政部門', '業務部門']),
            'position' => $faker->randomElement(['經理', '專員', '主任', '助理']),
            'emergency_contact' => $faker->name(),
            'emergency_phone' => '09' . $faker->numerify('########'),
        ];
    }

    /**
     * 生成有效的台灣身分證字號
     */
    private function generateTaiwanId(): string
    {
        $areas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $area = fake()->randomElement($areas);
        $gender = fake()->randomElement([1, 2]); // 1=男, 2=女
        $serial = str_pad(fake()->numberBetween(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // 計算檢查碼
        $areaValues = [
            'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
            'I' => 34, 'J' => 18, 'K' => 19, 'L' => 20, 'M' => 21, 'N' => 22, 'O' => 35, 'P' => 23,
            'Q' => 24, 'R' => 25, 'S' => 26, 'T' => 27, 'U' => 28, 'V' => 29, 'W' => 32, 'X' => 30,
            'Y' => 31, 'Z' => 33
        ];
        
        $sum = intval($areaValues[$area] / 10) + ($areaValues[$area] % 10) * 9;
        $sum += $gender * 8;
        
        for ($i = 0; $i < 6; $i++) {
            $sum += intval($serial[$i]) * (7 - $i);
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $area . $gender . $serial . $checkDigit;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
