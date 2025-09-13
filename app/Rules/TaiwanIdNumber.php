<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TaiwanIdNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValidTaiwanId($value)) {
            $fail('身分證字號格式不正確');
        }
    }

    /**
     * 驗證台灣身分證字號
     */
    private function isValidTaiwanId(string $id): bool
    {
        // 基本格式檢查：第一個字母，後面9個數字
        if (! preg_match('/^[A-Z][0-9]{9}$/', $id)) {
            return false;
        }

        // 字母對應數字表
        $letterValues = [
            'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15,
            'G' => 16, 'H' => 17, 'I' => 34, 'J' => 18, 'K' => 19, 'L' => 20,
            'M' => 21, 'N' => 22, 'O' => 35, 'P' => 23, 'Q' => 24, 'R' => 25,
            'S' => 26, 'T' => 27, 'U' => 28, 'V' => 29, 'W' => 32, 'X' => 30,
            'Y' => 31, 'Z' => 33,
        ];

        $letter = $id[0];
        $numbers = substr($id, 1);

        // 轉換字母為數字
        $letterValue = $letterValues[$letter];
        $sum = intval($letterValue / 10) + ($letterValue % 10) * 9;

        // 計算加權和
        $weights = [8, 7, 6, 5, 4, 3, 2, 1, 1];
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($numbers[$i]) * $weights[$i];
        }

        // 檢查檢查碼
        return ($sum % 10) === 0;
    }
}
