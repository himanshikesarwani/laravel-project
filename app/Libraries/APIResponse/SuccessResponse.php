<?php
namespace App\Libraries\APIResponse;

class SuccessResponse extends BaseResponse
{
     /*
     * |--------------------------------------------------------------------------
     * | Class for success response
     * |--------------------------------------------------------------------------
     * |
     */

    /**
     * Contain extra fillables in comparison to baseResponse class.
     *
     * @var array
     */
    protected $extraFillables = ['data'];

    public function __construct()
    {
        $this->fillable = array_merge($this->fillable,$this->extraFillables);
    }
}
