<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'guard_name', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modelHasRoles()
    {
        return $this->hasMany(ModelHasRole::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    /* public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    } */
}
