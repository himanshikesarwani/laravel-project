<?php
namespace App\Modules\User\Providers;

use App\Modules\User\Repositories\Permissions\PermissionRepository;
use Illuminate\Support\ServiceProvider;
use App\Entities\Permission;

class PermissionRepositoryServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | PermissionRepositoryServiceProvider
     * |--------------------------------------------------------------------------
     * | Registers the PermissionInterface with Laravels IoC Container
     * |
     */
    
    /**
     * Registers the PermissionInterface with Laravels IoC Container
     * 
     * @return PermissionRepository
     */
    public function register()
    {
        // Bind the returned class to the namespace 'App\Modules\User\Repositories\Permissions\PermissionInterface
        $this->app->bind('App\Modules\User\Repositories\Permissions\PermissionInterface', function ($app) {
            return new PermissionRepository(new Permission());
        });
    }
}