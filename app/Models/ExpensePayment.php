<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpensePayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'record_date',
        'record_time',
        'driver_id',
        'vehicle_id',
        'member_code',
        'member_name',
        'vehicle_license_number',
        'item_name',
        'gross_amount',
        'deduction',
        'net_amount',
        'payment_date',
        'payment_method',
        'status',
        'note',
        'created_by',
        'updated_by',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'record_date' => 'date',
        'payment_date' => 'date',
        'gross_amount' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (auth()->check()) {
                $model->created_by ??= auth()->id();
                $model->updated_by ??= auth()->id();
            }
        });

        static::updating(function (self $model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopeKeyword($query, ?string $keyword)
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('member_name', 'like', "%{$keyword}%")
                ->orWhere('member_code', 'like', "%{$keyword}%")
                ->orWhere('vehicle_license_number', 'like', "%{$keyword}%")
                ->orWhere('item_name', 'like', "%{$keyword}%");
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeRecordDateBetween($query, ?string $start, ?string $end)
    {
        if (! $start && ! $end) {
            return $query;
        }

        if ($start && $end) {
            return $query->whereBetween('record_date', [$start, $end]);
        }

        if ($start) {
            return $query->where('record_date', '>=', $start);
        }

        return $query->where('record_date', '<=', $end);
    }

    public function scopePaymentDateBetween($query, ?string $start, ?string $end)
    {
        if (! $start && ! $end) {
            return $query;
        }

        if ($start && $end) {
            return $query->whereBetween('payment_date', [$start, $end]);
        }

        if ($start) {
            return $query->where('payment_date', '>=', $start);
        }

        return $query->where('payment_date', '<=', $end);
    }
}
