<?php
namespace App\Modules\User\Services\Facades;

use \Illuminate\Support\Facades\Facade;

class RoleFacade extends Facade
{

    /*
     * |--------------------------------------------------------------------------
     * | RoleFacade
     * |--------------------------------------------------------------------------
     * |Facade class to be called whenever the class RolePermissionService is called
     * |
     */
    
    /**
     * Get role and permissions.
     * This tells $this->app what record to return
     * (e.g. $this->app[‘RoleService’])
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return "RoleService";
    }
}

