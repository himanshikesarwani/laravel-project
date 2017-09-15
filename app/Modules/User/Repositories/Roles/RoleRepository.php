<?php
namespace App\Modules\User\Repositories\Roles;

use \stdClass;
use Illuminate\Database\Eloquent\Model;
use App\Modules\User\Repositories\Roles\RolePermissionInterface;
use phpDocumentor\Reflection\Types\Boolean;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class RoleRepository extends BaseRepository implements RolePermissionInterface
{

    /*
     * |--------------------------------------------------------------------------
     * | RoleRepository
     * |--------------------------------------------------------------------------
     * | Our permission repository, containing commonly used queries
     * |
     */

    // Our Eloquent roleModel model
    protected $roleModel;

    // Our Eloquent permissionRole model
    protected $permissionRoleModel;

    /**
     * Setting our class $roleModel to the injected model
     *
     * @param Model $roleModel
     * @param Model $permissionRoleModel
     */
    public function __construct(Model $roleModel, Model $permissionRoleModel)
    {
        parent::__construct($roleModel);
        $this->roleModel = $roleModel;
        $this->permissionRoleModel = $permissionRoleModel;
    }

    /**
     * Function to create a new model object
     *
     * @param array $roleArray
     * @return Model $roleModel
     */
    public function create(array $roleArray)
    {
        try {
            // set up role fields
            $this->roleModel->name = str_slug($roleArray['display_name'], '_');
            $this->roleModel->display_name = $roleArray['display_name'];
            $this->roleModel->description = $roleArray['description'];

            // call to eloquent to save role
            $this->roleModel->save();
            return $this->roleModel;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to update existing model object
     *
     * @param array $roleArray
     * @param array $conditions
     * @return Model $roleModel
     */
    public function update(array $roleArray, array $conditions)
    {
        try {
            // set up role fields
            $roleModel = $this->roleModel->where('name', '=', $conditions['slug'])->firstOrFail();
            $roleModel['display_name'] = $roleArray['display_name'];
            $roleModel['description'] = $roleArray['description'];

            // call to eloquent to save role
            $roleModel->save();
            return $roleModel;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to delete an existing model object
     *
     * @param array $conditions
     * @return Boolean
     */
    public function delete(array $conditions)
    {
        try {
            $roleModel = $this->roleModel->where('name', '=', $conditions['slug'])->firstOrFail();

            // call to eloquent to delete role
            $roleModel->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to fetch all roles
     *
     * @return roles
     */
    public function getRolesList()
    {
        try {
            // call to eloquent to get all roles
            $roles = $this->roleModel->orderBy('display_name')->get();
            return $roles;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Returns the role object associated with the passed slug
     *
     * @param string $roleSlug
     *
     * @return Model
     */
    public function getRoleBySlug($roleSlug)
    {
        try {
            // call to eloquent to get role
            return $this->convertFormat($this->roleModel->where('name', '=', $roleSlug)->firstOrFail());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Converting the Eloquent object to a standard format
     *
     * @param mixed $role
     * @return stdClass
     */
    protected function convertFormat($role)
    {
        if ($role == null) {
            return null;
        }

        $object = new stdClass();
        $object->id = $role->id;
        $object->name = $role->name;
        $object->display_name = $role->display_name;
        $object->description = $role->description;

        return $object;
    }

    /**
     * Function getRolePermissions is used to get all roles and there permissions
     *
     * @return role model obj
     */
    public function getRolePermissions()
    {
        try {
            $roles = $this->roleModel->all();

            /* loop is used to get permission of each role */
            foreach ($roles as $role) {
                $role->permissions = $role->permissions()->get();
            }
            return $roles;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Fucntion updateRolePermission is used to set mapping between role and permissions
     *
     * @params array $request
     *
     * @return boolean
     *
     */
    public function updateRolePermission(array $request)
    {
        try {
            // set model attributes
            $this->permissionRoleModel->permission_id = $request['permission'];
            $this->permissionRoleModel->role_id = $request['role'];
            // call to eloquent to save role permission mapping
            $rolePermsssionResponse = $this->permissionRoleModel->save();

            return $rolePermsssionResponse;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Fucntion deleteRolePermission is used to un-mapping role and permission
     *
     * @params array $request
     *
     * @return boolean
     *
     */
    public function deleteRolePermission(array $request)
    {
        try {
            // set model attributes
            $this->permissionRoleModel->permission_id = $request['permission'];
            $this->permissionRoleModel->role_id = $request['role'];

            // call to eloquent to revoke role permission mapping
            $rolePermsssionResponse = $this->permissionRoleModel->where('permission_id', '=', $request['permission'])
                ->where('role_id', '=', $request['role'])
                ->delete();

            return $rolePermsssionResponse;
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
    * Function get roles list by permission
    *
    * @param array $request
    * @return object
    */
    public function getRoleByPermission(array $request)
    {
        try {
            $userId = $request['userId'];
            $permissions = $request['permissions'];
            // call stored procedure for user role permission
            $result = DB::select("call people_role_hierarchy($userId, '$permissions')");
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
    * Function to get role by user's id
    *
    * @param int $userId
    *
    * @return object
    */
    public function getRoleByUserId($userId)
    {
        try {
            //join the role_user & roles table to get the name of the role
           // on the basis of user id
            $result = DB::table('role_user AS ru')
                        ->join('roles AS r', function ($join) {
                            $join->on('ru.role_id', '=', 'r.id');
                        })
                        ->select('r.name')
                        ->where('ru.user_id', $userId)->first();
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
    * Function to get role by consultation id
    *
    * @param int $consultationId
    *
    * @return object
    */
    public function getRoleByConsultationId($consultationId)
    {
        try {
            // Join the consultations & appointment & user_roles & roles table
            // to get the user's role from the consultation id
            $result = DB::table('appointments AS a')
                ->join('role_user AS ru', function ($join) {
                    $join->on('ru.user_id', '=', 'a.expert_id');
                })
                ->join('roles AS r', function ($join) {
                    $join->on('ru.role_id', '=', 'r.id');
                })
                ->select('r.name')
                ->where('a.consultations_id', $consultationId)
                ->first();
                return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
