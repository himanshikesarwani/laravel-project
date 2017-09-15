<?php
namespace App\Libraries\APIResponse;

use Illuminate\Database\Eloquent\Model;

class BaseResponse extends Model
{
     /*
     * |--------------------------------------------------------------------------
     * | Base class for API reponse
     * |--------------------------------------------------------------------------
     * |
     */

    /**
     * Array for mass-assignment.
     *
     * @var array
     */
     protected $fillable = [
        'status',
        'code',
        'message'
     ];

}
