<?php

namespace Jarwis\models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $role_id
 * @property string $model_type
 * @property integer $model_id
 * @property Role $role
 */
class ModelHasRole extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id'];
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
}
