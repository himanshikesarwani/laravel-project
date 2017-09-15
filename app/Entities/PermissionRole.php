<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
	protected $fillable=['role_id','permission_id'];
	
    /**
    * Name of the table associated with this model.
    *
    * @var string
    */
    protected $table = 'permission_role';
    
    public $timestamps = false;
}
