<?php
namespace App\Modules\User\Repositories\Roles;

use Illuminate\Database\Eloquent\Model;

interface RolePermissionInterface
{

    /**
     * Function to getRolePermissions get all permssions and roles
     *
     * @return Model $object
     */
    public function getRolePermissions();
    
    /**
    * Function to get role on basis of permission
    *
    * @param array $request
    */
    public function getRoleByPermission(Array $request);
}
