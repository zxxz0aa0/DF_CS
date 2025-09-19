<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\CompanyCategory;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Excel;

class VehicleTemplateExport implements WithMultipleSheets, Responsable
{
    use Exportable;

    private $fileName = 'vehicle_import_template.xlsx';
    private $writerType = Excel::XLSX;

    public function sheets(): array
    {
        return [
            '車輛範本' => new VehicleTemplateSheet(),
            '公司類別參考' => new CompanyCategoryReferenceSheet(),
            '公司參考' => new CompanyReferenceSheet(),
        ];
    }
}

class VehicleTemplateSheet implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            '車牌號碼*',
            '替補車號',
            '車主名稱*',
            '公司類別*',
            '公司名稱*',
            '車輛類型',
            '地址',
            '車輛廠牌',
            '出廠年',
            '出廠月',
            '車輛形式',
            '排氣量',
            '燃料種類',
            '車輛款式',
            '車輛樣式',
            '引擎號碼',
            '車身號碼',
            '載運人數',
            '車輛顏色',
            '發照年(民國)',
            '發照月',
            '發照日',
            '檢驗年(民國)',
            '檢驗月',
            '檢驗日',
            '入籍年(民國)',
            '入籍月',
            '入籍日',
            '產權類別',
            '備註',
            '狀態',
        ];
    }

    public function array(): array
    {
        return [
            [
                'ABC-1234',
                'DEF-5678',
                '範例車主',
                '運輸公司',
                '台北運輸股份有限公司',
                '小客車',
                '台北市信義區市府路1號',
                'Toyota',
                '2020',
                '5',
                '轎車',
                '2000',
                '汽油',
                'Camry',
                '四門',
                'ENG123456',
                'CHA789012',
                '5',
                '白色',
                '113',
                '6',
                '15',
                '113',
                '12',
                '31',
                '113',
                '1',
                '1',
                '自有',
                '範例備註',
                '在籍',
            ]
        ];
    }
}

class CompanyCategoryReferenceSheet implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['公司類別名稱', 'ID'];
    }

    public function array(): array
    {
        $categories = CompanyCategory::all(['name', 'id']);
        return $categories->map(function ($category) {
            return [$category->name, $category->id];
        })->toArray();
    }
}

class CompanyReferenceSheet implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['公司名稱', 'ID', '公司類別'];
    }

    public function array(): array
    {
        $companies = Company::with('category')->get(['name', 'id', 'category_id']);
        return $companies->map(function ($company) {
            return [
                $company->name,
                $company->id,
                $company->category?->name ?? ''
            ];
        })->toArray();
    }
}