<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends SpatieRole
{
    /**
     * 角色下的職務
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
