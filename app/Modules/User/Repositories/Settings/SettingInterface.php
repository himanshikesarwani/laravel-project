<?php
namespace App\Modules\User\Repositories\Settings;

use Illuminate\Database\Eloquent\Model;

interface SettingInterface
{

    /**
     * Function to get all settings
     *
     * @return Model $object
     */
    public function getSettings();

    /**
     * Function to get config value
     *
     * @return string
     */
    public function getConfigValue();
}
