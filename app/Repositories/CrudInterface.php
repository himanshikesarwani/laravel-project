<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

interface CrudInterface
{
    /**
     * Function to create a new model object
     *
     * @param array $model
     * @return Model $object
     */
    public function create(array $model);
}
