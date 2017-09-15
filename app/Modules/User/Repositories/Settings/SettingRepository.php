<?php
namespace App\Modules\User\Repositories\Settings;

use Illuminate\Database\Eloquent\Model;
use App\Modules\User\Repositories\Settings\SettingInterface;
use App\Repositories\BaseRepository;

class SettingRepository extends BaseRepository implements SettingInterface
{

    /*
     * |--------------------------------------------------------------------------
     * | SettingRepository
     * |--------------------------------------------------------------------------
     * | Our setting repository, containing commonly used queries
     * |
     */
    
    /**
     *
     * @var Setting model
     */
    protected $settingModel;

    /**
     * Setting our class $settingModel to the injected model
     *
     * @param Model settingModel
     *
     * @return SettingRepository
     */
    public function __construct(Model $settingModel)
    {
        parent::__construct($settingModel);
        $this->settingModel = $settingModel;
    }

    /**
     * Function to set config in array
     *
     * @return array
     */
    private function setConfigArray()
    {
        echo 'hiii';
    }

    /**
     * Function getSettings is used to get all settings
     *
     * @return setting array
     */
    public function getSettings()
    {
        try {
            return $this->settingModel->lists('value', 'key');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to format setting value
     *
     * @param object $setting
     *
     * @return string
     */
    protected function formatData($setting)
    {
        if ($setting == null) {
            return null;
        }
        return $setting->value;
    }

    /**
     * Function to get config value from database
     *
     * @param string $key
     *
     * @return string
     */
    public function getConfigValue($key = null)
    {
        try {
            $data = $this->settingModel->where('key', ($key));
            if ($data) {
                return $this->formatData($data->first());
            }
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
