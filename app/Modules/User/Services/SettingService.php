<?php
namespace App\Modules\User\Services;

use Cache;
use Illuminate\Support\Facades\Config as Config;
use App\Modules\User\Repositories\Settings\SettingInterface;
use App\Services\BaseService;

class SettingService extends BaseService
{

    /*
     * |--------------------------------------------------------------------------
     * | SettingService
     * |--------------------------------------------------------------------------
     * | SettingService class containing all useful methods for business logic around setting
     * |
     */

    /**
     *
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * Function to set setting repository object
     *
     * @param SettingInterface $settingRepository
     */
    public function __construct(SettingInterface $settingRepository)
    {
        parent::__construct($settingRepository);
        $this->settingRepository = $settingRepository;
    }

    /**
     * Method to get settings
     *
     * @return array
     */
    public function getSettings()
    {
        try {
            $settingResult = $this->settingRepository->getSettings();
            Cache::flush();
            Cache::putMany($settingResult->toArray(), Config::get('cache.lifetime'));
            return $settingResult;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Method to get config value
     *
     * @param string $key
     *
     * @return string
     */
    public function getConfigValue($key)
    {
        try {
            if (isset($key) && ! empty($key)) {
                if (Cache::has($key)) {
                    $settingVal = Cache::get($key);
                } else {
                    $settingVal = $this->settingRepository->getConfigValue($key);
                    Cache::put($key, $settingVal, Config::get('cache.lifetime'));
                }
                return $settingVal;
            } else {
                throw new \Exception(sprintf("$key  not Found."));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Method to get config value using magic method
     *
     * @param string $method
     * @param array $args
     *
     * @return string
     */
    public function __call($method, $args)
    {
        if ($method) {
            //split string in array
            $pieces = preg_split('/(?=[A-Z])/', $method);
            // check array has value "get" and then unset from array
            if (($key = array_search('get', $pieces)) !== false) {
                unset($pieces[$key]);
            }

            // create string from array and convert string to lowercase
            $key_name = strtolower(implode("_", $pieces));

            // check for arguments
            if (count($args) > 0 && is_array($args) && $key_name == 'key') {
                $key_name = implode($args);
            }

            return $this->getConfigValue($key_name);
        }
    }
}
