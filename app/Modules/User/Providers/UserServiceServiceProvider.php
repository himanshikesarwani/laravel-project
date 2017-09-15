<?php
namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Services\UserService;

class UserServiceServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | UserServiceServiceProvider
     * |--------------------------------------------------------------------------
     * |Register our User service with Laravel
     * |
     */
    
    /**
     * Registers the service in the IoC Container
     *
     * @return UserService
     */
    public function register()
    {
        // Binds 'UserServices' to the result of the closure
        $this->app->bind('UserService', function ($app) {
            return new UserService($app->make('App\Modules\User\Repositories\User\UserInterface'));
        });
    }
}
