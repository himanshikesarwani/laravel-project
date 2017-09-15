<?php

namespace App\Http\Controllers\V1\Web\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Modules\User\Services\Facades\PermissionFacade;
use App\Modules\User\Services\Facades\RoleFacade;
use Exception;
use App\Http\Controllers\ApiBaseController as BaseController;
use App\Libraries\HelpersFacade as Helpers;
use stdClass;
use Illuminate\Contracts\Support\MessageBag;

class PermissionController extends BaseController
{

    /*
     * |--------------------------------------------------------------------------
     * | PermissionController
     * |--------------------------------------------------------------------------
     * |
     * | Here is where you can manage role permissiosns, modify existing permissiosns
     * | and add new permission
     * |
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            //calling the permission facade function to create a new permission
            $permission = PermissionFacade::create($request->all());

            //check if validation errors occured or not
            if ($permission instanceof \Illuminate\Validation\Validator) {
                //return the validation errors to the user
                return Helpers::errorResponseHandler('validation error', 40103, 401, $permission->errors());
            }

            $permissionRespone = array(
                'code'=> config('api-config.SUCCESS_CODE'),
                'message' => config('messages.PERMISSION_STORE_SUCCESS'),
                'data'  => $permission,

            );

            //return the success response to the user
            return Helpers::successResponseHandler($permissionRespone);
        } catch (\Exception $e) {
            //return the error message to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request)
    {
        try {
            //get the id from the request
            $id = $request->get('id');

            //call the findbyId function of permission facade
            $permission = PermissionFacade::findById($id);
            if (!empty($permission)) {
                //return the permission data
                return Helpers::successResponseHandler('Success', $permission);
            }

            return Helpers::errorResponseHandler('error', 40103, 401);

        } catch (\Exception $e) {
            //return the error message to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function update(Request $request)
    {
        try {
            //passing the request data to the permission facade method
            $permission = PermissionFacade::update($request->all());

            //check if validation error occured or not
            if ($permission instanceof \Illuminate\Validation\Validator) {
                //return the validation errors to the user
                return Helpers::errorResponseHandler('error', 40103, 401, $permission->errors());
            }
            //return the permission data to the user
            return Helpers::successResponseHandler('Success', $permission);
        } catch (\Exception $e) {
            //return the error message to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        try {
            //pass the permission id to the facade function call
            $permissions = PermissionFacade::delete($request->get('permission_id'));

            //return the deletion success response
            return Helpers::successResponseHandler('Success', $permissions);
        } catch (\Exception $e) {
            //return the error message to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * This Function is used to list all permissions in the view.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request)
    {
        try {
            //fetch all the permission from the database
            $permissions = PermissionFacade::getPermissions();

            //return the permission index view with the permission data
            return Helpers::successResponseHandler([
                'message' => 'Success',
                'data' => array('permissions' => $permissions)
            ]);

        } catch (\Exception $e) {
                // exception occured, return to the page not found error page
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Function editRolePermissions is used to update role permission mapping
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editRolePermissions(Request $request)
    {
        try {
            $requestData = $request->all();

            // call to service method updateRolePermissions through Facade
            $rolesPermissionsResult = RoleFacade::updateRolePermissions($requestData);

            // check if validation errors occured or not
            if ($rolesPermissionsResult instanceof MessageBag) {
                $failureResponse = array(
                    "message" => config('messages.VALIDATION_FAILED.message'),
                    "code" => config('messages.VALIDATION_FAILED.errorCode'),
                    "data" => $rolesPermissionsResult->messages()
                );

                return Helpers::failureResponseHandler($failureResponse, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            }

            if (! empty($rolesPermissionsResult)) {
                // convert array to json object and then return
                return Helpers::successResponseHandler([
                    'message' => 'Success',
                    'data' => $rolesPermissionsResult
                ]);
            }

                // return error
                return Helpers::failureResponseHandler(array(
                    'message' => 'Error',
                    'data' => array()
                ), 402);
        } catch (\Exception $e) {
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }
}
