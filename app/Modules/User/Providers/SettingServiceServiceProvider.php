<?php
namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Services\SettingService;

class SettingServiceServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | SettingServiceServiceProvider
     * |--------------------------------------------------------------------------
     * |Register our setting service with Laravel
     * |
     */
    
    /**
     * Registers the service in the IoC Container
     *
     * @return UserService
     */
    public function register()
    {
        // Binds 'SettingService' to the result of the closure
        $this->app->bind('SettingService', function ($app) {
            return new SettingService($app->make('App\Modules\User\Repositories\Settings\SettingInterface'));
        });
    }
}
