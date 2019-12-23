<?php

namespace Jarwis\models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
	protected $guarded = ['id'];

    public $incrementing = false;

    function getDataAttribute(){
        if(isset($this->attributes['data'])){
            return json_decode($this->attributes['data'])->notification;
        }else{
            return "";
        }
    }
    
    function setDataAttribute($value){
    	$this->attributes['data'] = json_encode($value);
    }
    
}
