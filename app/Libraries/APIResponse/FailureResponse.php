<?php
namespace App\Libraries\APIResponse;



class FailureResponse extends BaseResponse
{

    /*
     * |--------------------------------------------------------------------------
     * | Class for exception response
     * |--------------------------------------------------------------------------
     * |
     */

    protected $extraFillables = ['data'];

    /**
     * Contain fillable properties.
     *
     * @var array
     */
    private $fillableProperties;

    public function __construct()
    {
        $this->fillable = array_merge($this->fillable,$this->extraFillables);
    }
}