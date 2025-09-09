<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class, 'category_id');
    }
}
