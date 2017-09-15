<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository implements CrudInterface
{

    /*
     * |--------------------------------------------------------------------------
     * | BaseReporitory
     * |--------------------------------------------------------------------------
     * |
     * |
     */
    // Eloquent model
    protected $model;

    /**
     * Constructor injected with model
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Function to create a new model object
     *
     * @param array $model
     * @return Model $object
     */
    public function create(Array $model)
    {

    }
}
