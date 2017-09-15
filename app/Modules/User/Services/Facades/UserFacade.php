<?php
namespace App\Modules\User\Services\Facades;

use \Illuminate\Support\Facades\Facade;

class UserFacade extends Facade
{

    /*
     * |--------------------------------------------------------------------------
     * | UserFacade
     * |--------------------------------------------------------------------------
     * |Facade class to be called whenever the class UserService is called
     * |
     */
    
    /**
     * Get user.
     * This tells $this->app what record to return
     * (e.g. $this->app[‘UserServices’])
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return "UserService";
    }
}

