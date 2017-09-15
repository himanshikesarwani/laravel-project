<?php
namespace App\Modules\User\Services\Facades;

use \Illuminate\Support\Facades\Facade;

class PermissionFacade extends Facade
{

    /*
     * |--------------------------------------------------------------------------
     * | PermissionFacad
     * |--------------------------------------------------------------------------
     * |Facade class to be called whenever the class PermissionService is called
     * |
     */
    
    /**
     * Get permissions.
     * This tells $this->app what record to return
     * (e.g. $this->app[‘PermissionService’])
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return "PermissionService";
    }
}

