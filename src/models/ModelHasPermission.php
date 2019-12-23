<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $permission_id
 * @property string $model_type
 * @property integer $model_id
 * @property Permission $permission
 */
class ModelHasPermission extends Model
{
    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    // public function permission()
    // {
    //     return $this->belongsTo(Permission::class);
    // }
}
