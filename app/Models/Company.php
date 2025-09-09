<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'address',
        'tax_id',
        'phone',
        'representative',
        'email',
        'website',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(CompanyCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
