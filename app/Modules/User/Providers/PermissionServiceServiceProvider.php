<?php
namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Services\PermissionService;

class PermissionServiceServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | PermissionServiceServiceProvider
     * |--------------------------------------------------------------------------
     * |Register our Permission service with Laravel IoC Container
     * |
     */
    
    /**
     * Registers the service in the IoC Container
     */
    public function register()
    {
        // Binds 'PermissionService' to the result of the closure
        $this->app->bind('PermissionService', function ($app) {
            return new PermissionService(
                // Inject in our class of PermissionInterface, this will be our repository
                $app->make('App\Modules\User\Repositories\Permissions\PermissionInterface'));
        });
    }
}