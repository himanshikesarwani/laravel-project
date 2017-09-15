<?php
namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use App\Entities\User;
use App\Modules\User\Repositories\User\UserRepository;

class UserRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registers the UserInterface with Laravels IoC Container
     *
     * @return UserRepository
     */
    public function register()
    {
        // Bind the returned class to the namespace 'App\Modules\User\Repositories\User\UserInterface
        $this->app->bind('App\Modules\User\Repositories\User\UserInterface', function ($app) {
            return new UserRepository(new User());
        });
    }
}
