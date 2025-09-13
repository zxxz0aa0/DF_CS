<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

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
