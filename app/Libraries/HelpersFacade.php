<?php
namespace App\Libraries;

use \Illuminate\Support\Facades\Facade;

class HelpersFacade extends Facade
{

    /*
     * |--------------------------------------------------------------------------
     * | Facade Class for repository
     * |--------------------------------------------------------------------------
     * |
     * | Facade class to be called whenever the class UserService is called
     * |
     */

    /**
     * Get the registered name of the component.
     * This tells $this->app what record to return
     * (e.g. $this->app[‘UserService’])
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {   
        return 'App\Libraries\ApplicationHelpers';
    }
}
