<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Excel;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping, Responsable, ShouldAutoSize
{
    use Exportable;

    private $fileName = 'vehicles.xlsx';
    private $writerType = Excel::XLSX;
    private $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Vehicle::with(['companyCategory', 'company']);

        // 應用篩選條件
        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        if (!empty($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('vehicle_status', $this->filters['status']);
        }

        if (!empty($this->filters['company_category_id'])) {
            $query->byCompanyCategory($this->filters['company_category_id']);
        }

        if (!empty($this->filters['company_id'])) {
            $query->byCompany($this->filters['company_id']);
        }

        if (!empty($this->filters['expiring_inspection'])) {
            $query->expiringInspection(30);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            '車牌號碼',
            '替補車號',
            '車主名稱',
            '公司類別',
            '公司名稱',
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
            '退籍年(民國)',
            '退籍月',
            '退籍日',
            '產權類別',
            '備註',
            '狀態',
            '建立時間',
            '更新時間',
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->license_number,
            $vehicle->replacement_license,
            $vehicle->owner_name,
            $vehicle->companyCategory?->name,
            $vehicle->company?->name,
            $vehicle->vehicle_type,
            $vehicle->address,
            $vehicle->brand,
            $vehicle->manufacture_year,
            $vehicle->manufacture_month,
            $vehicle->vehicle_form,
            $vehicle->engine_displacement,
            $vehicle->fuel_type,
            $vehicle->vehicle_model,
            $vehicle->vehicle_style,
            $vehicle->engine_number,
            $vehicle->chassis_number,
            $vehicle->passenger_capacity,
            $vehicle->vehicle_color,
            $vehicle->license_issue_year ? ($vehicle->license_issue_year - 1911) : null,
            $vehicle->license_issue_month,
            $vehicle->license_issue_day,
            $vehicle->inspection_year ? ($vehicle->inspection_year - 1911) : null,
            $vehicle->inspection_month,
            $vehicle->inspection_day,
            $vehicle->registration_year ? ($vehicle->registration_year - 1911) : null,
            $vehicle->registration_month,
            $vehicle->registration_day,
            $vehicle->deregistration_year ? ($vehicle->deregistration_year - 1911) : null,
            $vehicle->deregistration_month,
            $vehicle->deregistration_day,
            $vehicle->property_type,
            $vehicle->notes,
            $vehicle->status_text,
            $vehicle->created_at?->format('Y-m-d H:i:s'),
            $vehicle->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}