<?php
namespace App\Modules\User\Services;

use Illuminate\Http\Request;
use Validator;
use App\Modules\User\Repositories\Permissions\PermissionInterface;
use App\Services\BaseService;

class PermissionService extends BaseService
{

    /*
     * |--------------------------------------------------------------------------
     * | PermissionServices
     * |--------------------------------------------------------------------------
     * |PermissionServices class containing all useful methods for business logic around Permission
     * |
     */

    /**
     *
     * @var permissionsRepository
     */
    protected $permissionsRepository;

    /**
     * Function to set permission repository object
     *
     * @param permissionInterface $permissionRepo
     * @return PermissionService
     */
    public function __construct(PermissionInterface $permissionsRepository)
    {
        parent::__construct($permissionsRepository);
        $this->permissionsRepository = $permissionsRepository;
    }

    /**
     * function getRolePermissions used to get all linked roles and permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        // try {
        // fetch all the permissions from the datbase
        return $permissionResult = $this->permissionsRepository->getPermissions();
        // } catch (\Exception $e) {
        // //exception occured, throw it to the controller
        // throw (new \Psy\Exception\ErrorException());
        // }
    }

    /**
     * Function to store the permission in the database.
     *
     * @param array $request
     *
     * @return void
     */
    public function create(array $request)
    {
        try {
            // the validation rules for the request data
            $rules = [
                'display_name' => 'required|string',
                'description' => 'string'
            ];

            // Checking the rules validity
            $validator = Validator::make($request, $rules);

            // if validation failed return errors
            if ($validator->fails()) {
                return $validator;
            }

            // creating url friendly slug from the display_name variable
            $request['name'] = str_slug($request['display_name'], "_");

            // the validation rules for the request data
            $rules = [
                'name' => 'required|string|unique:permissions'
            ];

            // Checking the rules validity
            $validator = Validator::make($request, $rules);

            // if validation failed return errors
            if ($validator->fails()) {
                return $validator;
            }

            // create & return the permission
            return $this->permissionsRepository->create($request);
        } catch (\Exception $e) {
            // exception occured, throwing it to the controller
            throw $e;
        }
    }

    /**
     * Function to find a permission using it's id from the database.
     *
     * @param int $id
     *
     * @return Model
     */
    public function findById($id = null)
    {
        try {
            // the validation rules for the request data
            $rules = [
                'id' => 'required'
            ];

            // Checking the rules validity
            $validator = Validator::make([
                'id' => $id
            ], $rules);

            // if validation failed return errors
            if ($validator->fails()) {
                return $validator;
            }

            // return the permission's data recieved from repository
            return $this->permissionsRepository->findById($id);
        } catch (\Exception $e) {
            // exception occured, throwing it to the controller
            throw $e;
        }
    }

    /**
     * Function to update a permission using it's id in the database.
     *
     * @param array $request
     * @param int $id
     *
     * @return Model
     */
    public function update(array $request)
    {
        try {
            // setting the permission id from request in $id variable
            $id = isset($request['id']) && ! empty($request['id']) ? $request['id'] : '';

            // checking the existance of name key in request data
            if (isset($request['name'])) {
                // remove the name key from the request data so that it can't be changed
                unset($request['name']);
            }

            // rules to validate the request data for permission update
            $rules = [
                'id' => 'required',
                'display_name' => 'required|string|unique:permissions,name,' . $id . ',id',
                'description' => 'string'
            ];

            // making the validator object to validate the request data
            $validator = Validator::make($request, $rules);

            // check if the validation failed or not
            if ($validator->fails()) {
                // return the errors to the user
                return $validator;
            }

            // return the response of repository's update operation
            return $this->permissionsRepository->update($request, $id);
        } catch (\Exception $e) {
            // exception occured, throwing it to the controller
            throw $e;
        }
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
        try {
            // the validation rules for the request data
            $rules = [
                'id' => 'required'
            ];

            // Checking the rules validity
            $validator = Validator::make([
                'id' => $id
            ], $rules);

            // if validation failed return errors
            if ($validator->fails()) {
                return $validator;
            }

            // return the response od permission deletion
            return $this->permissionsRepository->delete($id);
        } catch (\Exception $e) {
            // exception occured, throwing it to the controller
            throw $e;
        }
    }
}
