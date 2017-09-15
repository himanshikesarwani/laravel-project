<?php
namespace App\Modules\User\Providers;

use App\Modules\User\Repositories\Roles\RoleRepository;
use Illuminate\Support\ServiceProvider;
use App\Entities\Role;
use App\Entities\PermissionRole;

class RoleRepositoryServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | RoleRepositoryServiceProvider
     * |--------------------------------------------------------------------------
     * | Registers the RolePermissionInterface with Laravels IoC Container
     * |
     */
    
    /**
     * Registers the RolePermissionInterface with Laravels IoC Container
     * 
     * @return RoleRepository
     */
    public function register()
    {
        // Bind the returned class to the namespace 'App\Modules\User\Repositories\RolePermissionInterface
        $this->app->bind('App\Modules\User\Repositories\Roles\RolePermissionInterface', function ($app) {
            return new RoleRepository(new Role(),new PermissionRole());
        });
    }
}
