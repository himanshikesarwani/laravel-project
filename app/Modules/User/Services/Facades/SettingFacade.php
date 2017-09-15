<?php
namespace App\Modules\User\Services\Facades;

use \Illuminate\Support\Facades\Facade;

class SettingFacade extends Facade
{

    /*
     * |--------------------------------------------------------------------------
     * | SettingFacade
     * |--------------------------------------------------------------------------
     * |Facade class to be called whenever the class SettingService is called
     * |
     */
    
    /**
     * Get settings.
     * This tells $this->app what record to return
     * (e.g. $this->app[‘SettingService’])
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return "SettingService";
    }
}
