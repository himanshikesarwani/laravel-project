<?php
namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\EntrustUserTrait;


class User extends Model
{
    use EntrustUserTrait; 

    /**
     * Table Name
     *
     * @var $table
     */
    protected $table = 'users';

    /**
     *
     * @var $fillable
     */
    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'ums_guid',
        'status',
        'is_super_admin',
        'created_by'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * Function to map Role association
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function role()
    {
        return $this->hasOne(Role::class);
    }
}
