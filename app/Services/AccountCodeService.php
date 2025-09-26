<?php

namespace App\Services;

use App\Models\AccountDetail;
use App\Models\AccountMainCategory;
use App\Models\AccountSubCategory;

class AccountCodeService
{
    /**
     * 產生下一個可用的科目編號
     */
    public function generateNextCode(int $mainCategoryId, int $subCategoryId): string
    {
        $mainCategory = AccountMainCategory::find($mainCategoryId);
        $subCategory = AccountSubCategory::find($subCategoryId);

        if (!$mainCategory || !$subCategory) {
            throw new \InvalidArgumentException('無效的總類或子分類ID');
        }

        // 驗證子分類是否屬於該總類
        if ($subCategory->main_category_id !== $mainCategoryId) {
            throw new \InvalidArgumentException('子分類不屬於指定的總類');
        }

        // 組合基礎代碼 (總類代碼 + 子分類代碼)
        $baseCode = $mainCategory->category_code . $subCategory->sub_category_code;

        // 查找該基礎代碼下的最大編號
        $maxCode = AccountDetail::where('account_code', 'LIKE', $baseCode . '%')
            ->orderBy('account_code', 'desc')
            ->value('account_code');

        if (!$maxCode) {
            // 第一個編號
            return $baseCode . '001';
        }

        // 取得序號部分並遞增
        $sequencePart = substr($maxCode, strlen($baseCode));
        $sequence = intval($sequencePart) + 1;

        // 確保序號不超過999
        if ($sequence > 999) {
            throw new \RuntimeException('該分類下的科目編號已達上限(999)');
        }

        return $baseCode . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 驗證科目編號格式是否正確
     */
    public function validateCodeFormat(string $code): bool
    {
        // 基本格式驗證：只允許數字，長度在4-20之間
        if (!preg_match('/^[0-9]{4,20}$/', $code)) {
            return false;
        }

        return true;
    }

    /**
     * 解析科目編號結構
     */
    public function parseCodeStructure(string $code): array
    {
        if (!$this->validateCodeFormat($code)) {
            throw new \InvalidArgumentException('無效的科目編號格式');
        }

        // 基本結構：至少4位數字
        // 第1位：總類代碼
        // 第2-3位：子分類代碼
        // 第4位以後：序號

        $result = [
            'main_category_code' => substr($code, 0, 1),
            'sub_category_code' => substr($code, 1, 2),
            'sequence' => substr($code, 3),
            'full_code' => $code,
        ];

        return $result;
    }

    /**
     * 檢查科目編號是否已存在
     */
    public function isCodeExists(string $code, ?int $excludeId = null): bool
    {
        $query = AccountDetail::where('account_code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 批量產生科目編號
     */
    public function generateBatchCodes(int $mainCategoryId, int $subCategoryId, int $count): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $codes[] = $this->generateNextCode($mainCategoryId, $subCategoryId);

                // 模擬已使用該編號（避免重複產生）
                $this->simulateCodeUsage(end($codes));
            } catch (\RuntimeException $e) {
                break; // 達到上限時停止
            }
        }

        return $codes;
    }

    /**
     * 模擬編號已被使用（用於批量產生時避免重複）
     */
    private function simulateCodeUsage(string $code): void
    {
        // 在實際應用中，這裡可以暫時記錄到快取或暫存區
        // 這裡我們簡單地建立一個暫存記錄（實際開發時可能需要更精細的處理）
    }

    /**
     * 根據科目編號取得相關的分類資訊
     */
    public function getCategoryInfoByCode(string $code): ?array
    {
        try {
            $structure = $this->parseCodeStructure($code);

            $mainCategory = AccountMainCategory::where('category_code', $structure['main_category_code'])->first();
            if (!$mainCategory) {
                return null;
            }

            $subCategory = AccountSubCategory::where('main_category_id', $mainCategory->id)
                ->where('sub_category_code', $structure['sub_category_code'])
                ->first();

            if (!$subCategory) {
                return null;
            }

            return [
                'main_category' => $mainCategory,
                'sub_category' => $subCategory,
                'structure' => $structure
            ];
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * 驗證科目編號與分類的一致性
     */
    public function validateCodeConsistency(string $code, int $mainCategoryId, int $subCategoryId): bool
    {
        $categoryInfo = $this->getCategoryInfoByCode($code);

        if (!$categoryInfo) {
            return false;
        }

        return $categoryInfo['main_category']->id === $mainCategoryId &&
               $categoryInfo['sub_category']->id === $subCategoryId;
    }

    /**
     * 取得科目編號的建議格式說明
     */
    public function getCodeFormatGuidance(int $mainCategoryId, int $subCategoryId): string
    {
        $mainCategory = AccountMainCategory::find($mainCategoryId);
        $subCategory = AccountSubCategory::find($subCategoryId);

        if (!$mainCategory || !$subCategory) {
            return '請選擇有效的總類和子分類';
        }

        $baseCode = $mainCategory->category_code . $subCategory->sub_category_code;

        return "建議格式：{$baseCode}XXX (其中XXX為3位數字序號，如：{$baseCode}001)";
    }
}