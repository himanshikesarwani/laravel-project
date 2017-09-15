<?php
namespace App\Modules\User\Repositories\Permissions;

use Illuminate\Database\Eloquent\Model;
use App\Modules\User\Repositories\Permissions\PermissionInterface;
use App\Repositories\BaseRepository;




class PermissionRepository extends BaseRepository implements PermissionInterface
{

    /*
     * |--------------------------------------------------------------------------
     * | PermissionRepository
     * |--------------------------------------------------------------------------
     * | Our permission repository, containing commonly used queries
     * |
     */

    // Our Eloquent permissionsModel model
    protected $permissionsModel;

    /**
     * Setting our class $permissionsModel to the injected model
     *
     * @param Model permissionsModel
     *
     * @return PermissionRepository
     */
    public function __construct(Model $permissionsModel)
    {
        parent::__construct($permissionsModel);
        $this->permissionsModel = $permissionsModel;
    }

    /**
     * Function getPermissions is used to get all permissions
     *
     * @return Model
     */
    public function getPermissions()
    {
        try {
            return $this->permissionsModel->all();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to store a newly created permission in database
     *
     * @param array $data
     *
     * @return Model
     */
    public function create(array $data)
    {
        try {
            return $this->permissionsModel->create($data);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Function to find a permission using it's id from the database
     *
     * @param int $id
     *
     * @return Model
     */
    public function findById($id)
    {
        return $this->permissionsModel->find($id);
    }

    /**
     * Function to find a permission using it's slug from the database
     *
     * @param string $slug
     *
     * @return Model
     */
    public function findBySlug($slug)
    {
        try {
            // call to eloquent to get role
            return $this->permissionsModel->where('name', '=', $slug)->first();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to update a permission using it's id in the database.
     *
     * @param array $data
     * @param int $id
     *
     * @return Model
     */
    public function update($data, $id)
    {
        return $this->permissionsModel->find($id)->update($data);
    }

    /**
     * Function to delete a permission using it's id in the database.
     *
     * @param int $id
     *
     * @return Model
     */
    public function delete($id)
    {
        return $this->permissionsModel->find($id)->delete();
    }
}
