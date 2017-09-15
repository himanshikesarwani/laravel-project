<?php
namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Services\RoleService;

class RoleServiceServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | RoleServiceServiceProvider
     * |--------------------------------------------------------------------------
     * |Register our Role service with Laravel
     * |
     */
    
    /**
     * Registers the service in the IoC Container
     *
     * @return RoleServices
     */
    public function register()
    {
        // Binds 'RoleServices' to the result of the closure
        $this->app->bind('RoleService', function ($app) {
            return new RoleService(
                // Inject in our class of RolePermissionInterface, this will be our repository
                $app->make('App\Modules\User\Repositories\Roles\RolePermissionInterface'));
               
        });
    }
}
