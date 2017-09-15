<?php
namespace App\Services;

use App\Repositories\CrudInterFace;

class BaseService
{

    /*
     * |--------------------------------------------------------------------------
     * | BaseService
     * |--------------------------------------------------------------------------
     * | BaseService class containing all useful methods for business logic around BaseService
     * |
     */
    
    /**
     *
     * @var crudInterface
     */
    protected $baseRepository;
    
    /**
     * Function to construct crud interface object
     *
     * @param CrudInterface $crudInterface
     */
    public function __construct(CrudInterFace $baseRepository)
    {
        $this->baseRepository= $baseRepository;
    }
}
