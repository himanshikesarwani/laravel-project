<?php
namespace App\Modules\User\Repositories\Permissions;

use Illuminate\Database\Eloquent\Model;

interface PermissionInterface
{

    /**
     * Function to getPermissions get all permssions
     *
     * @return Model $object
     */
    public function getPermissions();

    /**
    * Function to store a newly created permission in database.
    *
    * @param array $data
    *
    * @return Model
    */
    public function create(array $data);

    /**
    * Function to find a permission using it's id from the database.
    *
    * @param int $id
    *
    * @return Model
    */
    public function findById($id);

    /**
     * Function to find a permission using it's slug from the database
     *
     * @param string $slug
     *
     * @return Model
     */
    public function findBySlug($slug);

    /**
    * Function to update a permission using it's id in the database.
    *
    * @param array $data
    * @param int $id
    *
    * @return Model
    */
    public function update($data, $id);

    /**
    * Function to delete a permission using it's id in the database.
    *
    * @param int $id
    *
    * @return Model
    */
    public function delete($id);
}
