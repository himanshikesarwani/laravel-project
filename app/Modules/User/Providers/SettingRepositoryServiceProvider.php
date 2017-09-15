<?php
namespace App\Modules\User\Providers;

use App\Entities\Setting;
use App\Modules\User\Repositories\Settings\SettingRepository;
use Illuminate\Support\ServiceProvider;

class SettingRepositoryServiceProvider extends ServiceProvider
{

    /*
     * |--------------------------------------------------------------------------
     * | SettingRepositoryServiceProvider
     * |--------------------------------------------------------------------------
     * | Registers the SettingInterface with Laravels IoC Container
     * |
     */
    
    /**
     * Registers the SettingInterface with Laravels IoC Container
     *
     * @return SettingRepository
     */
    public function register()
    {
        // Bind the returned class to the namespace 'Modules\User\Repositories\Settings\SettingInterface
        $this->app->bind('App\Modules\User\Repositories\Settings\SettingInterface', function ($app) {
            return new SettingRepository(new Setting());
        });
    }
}
