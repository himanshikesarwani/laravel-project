<?php
namespace App\Modules\User\Services;

use Validator;
use Illuminate\Http\Request;
use App\Modules\User\Repositories\Roles\RolePermissionInterface;
use App\Services\BaseService;

class RoleService extends BaseService
{

    /*
     * |--------------------------------------------------------------------------
     * | RoleService
     * |--------------------------------------------------------------------------
     * | RoleService class containing all useful methods for business logic around roles
     * |
     */

    /**
     *
     * @var roleRepository
     */
    protected $roleRepository;

    /**
     * Function to set role repository object
     *
     * @param RolePermissionInterface $roleRepository
     * @return null
     */
    public function __construct(RolePermissionInterface $roleRepository)
    {
        parent::__construct($roleRepository);
        $this->roleRepository = $roleRepository;
    }

    /**
     * Function to get all Roles
     *
     * @return Role List
     */
    public function getRolesList()
    {
        try {
            // Call a Function to fetch all roles
            $roles = $this->roleRepository->getRolesList();
            return $roles;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to get Role based on ID
     *
     * @param mixed $Role
     * @return Role
     */
    public function getRole($role)
    {
        try {
            // Get Role based on ID
            $role = $this->roleRepository->getRoleBySlug($role);
            return $role;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to save a new Role
     *
     * @param array $Role
     *
     * @return mixed
     */
    public function saveRole(array $request)
    {
        try {
            $input = array_map('trim', $request);

            $rules = [
                'display_name' => 'required|unique:roles,name|max:255|regex:/^[0-9A-Za-z\-_ ]+$/',
                'description' => 'string'
            ];

            // custom messages
            $messages = [
                'display_name.required' => config('messages.USER_ROLES.DISPLAY_NAME_REQUIRED'),
                'description.string' => config('messages.USER_ROLES.DISCRIPTION_STRING')
            ];
            $isValid = Validator::make($input, $rules, $messages);

            if ($isValid->fails()) {
                // in case error comes in validations
                return $isValid->errors();
            } else {
                // when no error occurs
                $role = $this->roleRepository->create($input);
                return $role;
            }

            // $validator = Validator::make($input, [
            // 'display_name' => 'required|unique:roles,name|max:255|regex:/^[0-9A-Za-z\-_ ]+$/',
            // 'description' => 'string'
            // ]);

            // Check validation and return if it fails
            // if (isset($validator) && $validator->fails()) {
            // return $validator;
            // }

            // // Calls to create function to save role
            // $role = $this->roleRepository->create($input);
            // return $role;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to update existing Role
     *
     * @param array $request
     * @param string $id
     * @return string
     */
    public function updateRole(array $request)
    {
        try {
            $slug = isset($request['slug']) && ! empty($request['slug']) ? $request['slug'] : '';
            $displayName = isset($request['display_name']) && ! empty($request['display_name']) ? $request['display_name'] : '';

            $validator = Validator::make($request, [
                'display_name' => 'required|max:255|regex:/^[0-9A-Za-z\-_ ]+$/|unique:roles,name,NULL,id,name,' . str_slug($displayName),
                'description' => 'string'
            ]);

            // Check validation and return if it fails
            if (isset($validator) && $validator->fails()) {
                return $validator;
            }

            // Calls to update function to save role
            $role = $this->roleRepository->update($request, array(
                'slug' => $slug
            ));
            return $role;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to delete Role based on ID
     *
     * @param string $id
     * @return boolean
     */
    public function deleteRole($slug)
    {
        try {
            // Calls to delete function to delete role
            $role = $this->roleRepository->delete(array(
                'slug' => $slug
            ));
            return $role;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * function getRolePermissions used to get all linked roles and permissions
     *
     * @return array
     */
    public function getRolePermissions()
    {
        try {
            // call to Repository method to get all roles,permission and there mapping
            return $rolePermissionsResult = $this->roleRepository->getRolePermissions();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Method to update Role permissions mapping
     *
     * @param Array $request
     * @return string
     */
    public function updateRolePermissions(array $request)
    {
        try {
            // condition set to assigmnet permissions or revoke permission to role
            $validator = Validator::make($request, array(
                'role' => 'required',
                'permission' => 'required',
                'status' => 'required'
            ));

            // Check validation and return if it fails
            if (isset($validator) && $validator->fails()) {
                return $validator->errors();
            }

            $response = array();

            $role = $this->getRole($request['role']);

            if (isset($role) && ! empty($role)) {
                $request['role'] = $role->id;
                // condition set to assigmnet permissions or revoke permission to role
                if (isset($request['status']) && ! empty($request['status'])) {
                    // call to Repository method to assign permission
                    $rolePermissions = $this->roleRepository->updateRolePermission($request);

                    // response array set
                    if ($rolePermissions) {
                        $response = array(
                            'permission' => true,
                            'message' => 'Permission Granted'
                        );
                    }
                } else {
                    // call to Repository method to revoke permission
                    $rolePermissions = $this->roleRepository->deleteRolePermission($request);
                    // response array set
                    if ($rolePermissions) {
                        $response = array(
                            'permission' => false,
                            'message' => 'Permission Revoked'
                        );
                    }
                }
            }
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to get roles on the basis of permission assigned
     *
     * @param array $request
     * @throws Exception
     * @return $roles
     */
    public function getRoleByPermission(array $request)
    {
        try {
            // Get Role based on ID
            $roles = $this->roleRepository->getRoleByPermission($request);

            if ($roles) {
                return $roles;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to now whether the user has access to the given resouce or not
     *
     * @param array $request
     * @throws Exception
     * @return $roles
     */
    public function getResourceAccessPermission(array $request)
    {
        try {
            // Get Role based on ID
            $roles = $this->getRoleByPermission($request);

            if ($roles && isset($request['resourceId']) && $request['permissions']) {
                $resourceRole = '';

                //perform diffrent operation on different permissions
                switch ($request['permissions']) {
                    case 'VIEW':
                    case 'CALENDARVIEW':
                    case 'CREATE_SESSION':
                    case 'CLEAR_SESSION':
                    case 'EDIT_SESSION':
                        $resourceRole = $this->roleRepository->getRoleByUserId($request['resourceId']);
                        break;

                    case 'CONSULTATIONLIST':
                        $resourceRole = $this->roleRepository->getRoleByConsultationId($request['resourceId']);
                        break;
                    case 'JOIN_CONSULTATION':
                        $resourceRole = $this->roleRepository->getRoleByConsultationId($request['resourceId']);
                        break;
                    case 'CANCEL_CONSULTATION':
                        $resourceRole = $this->roleRepository->getRoleByConsultationId($request['resourceId']);
                        break;

                    default:
                        $resourceRole = null;
                        break;
                }

                //array of roles object
                $rolesArray = json_decode(json_encode($roles), 1);

                //check if the resource's role is set or not
                if (isset($roles) && isset($resourceRole->name)) {
                    //check if the resourceRole exists in roles array or not
                    if (in_array($resourceRole->name, array_column($rolesArray, 'roles'))) {
                        return true;
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
