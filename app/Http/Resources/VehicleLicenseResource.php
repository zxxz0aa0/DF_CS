<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleLicenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ],
            'county' => $this->county,
            'license_number' => $this->license_number,
            'holder_name' => $this->holder_name,
            'license_date' => $this->license_date,
            'license_year' => $this->license_year,
            'license_month' => $this->license_month,
            'previous_license_number' => $this->previous_license_number,
            'previous_holder_name' => $this->previous_holder_name,
            'previous_license_date' => $this->previous_license_date,
            'previous_license_year' => $this->previous_license_year,
            'previous_license_month' => $this->previous_license_month,
            'previous_license_info' => $this->when($this->previous_license_number, [
                'number' => $this->previous_license_number,
                'holder' => $this->previous_holder_name,
                'date' => $this->previous_license_date,
                'year' => $this->previous_license_year,
                'month' => $this->previous_license_month,
            ]),
            'notes' => $this->notes,
            'replacement_date' => $this->replacement_date?->format('Y-m-d'),
            'revocation_date' => $this->revocation_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'is_active' => $this->isActive(),
            'is_revoked' => $this->isRevoked(),
            'is_transferred' => $this->isTransferred(),
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'updater' => [
                'id' => $this->updater?->id,
                'name' => $this->updater?->name,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'audit_logs_count' => $this->whenCounted('auditLogs'),
        ];
    }

    /**
     * 取得狀態標籤
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => '使用中',
            'revoked' => '已繳銷',
            'transferred' => '已轉移',
            'replaced' => '已替補',
            default => '未知狀態'
        };
    }
}
