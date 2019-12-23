<?php

namespace App\Models;

use App\Traits\FileUploadTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Backpack\CRUD\CrudTrait;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    // use CrudTrait;
    use FileUploadTrait;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /*protected $fillable = [
        'name', 'email', 'password',
    ];*/

    protected $guarded = ['id'];
    // protected $appends = ['name'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function deviceToken()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function setProfilePicAttribute($value)
    {
        $this->saveFile($value, 'profile_pic', "user/" . date('Y/m'), 'public');
    }
    
    public function getProfilePicAttribute()
    {
        if (!empty($this->attributes['profile_pic'])) {
            return Storage::disk('public')->url($this->attributes['profile_pic']);
        }
        return "";
    }

    public function notification()
    {
        return $this->hasMany(Notification::class,'notifiable_id','id');
    }

    public function getLastLoginAtAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->attributes['last_login_at']));
    }

    public function roleId()
    {
        return $this->hasOne('App\Models\ModelHasRole', 'model_id','id');
    }

}
