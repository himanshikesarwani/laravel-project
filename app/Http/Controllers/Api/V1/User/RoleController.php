<?php
namespace App\Http\Controllers\V1\Web\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \Illuminate\Validation\Validator;
use App\Modules\User\Services\Facades\RoleFacade as RoleFacade;
use App\Services\Helper\Facades\ApplicationHelperFacade;
use App\Http\Controllers\ApiBaseController as BaseController;
use App\Libraries\HelpersFacade as Helpers;
use Illuminate\Contracts\Support\MessageBag;

class RoleController extends BaseController
{

    /*
     * |--------------------------------------------------------------------------
     * | RoleController
     * |--------------------------------------------------------------------------
     * |
     * | Here is where you can add, edit, delete and list roles
     * |
     */

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        try {
            $roles = RoleFacade::getRolesList()->toArray();
            return Helpers::successResponseHandler(['message'=> 'Success','data'=> ['roles' => $roles]]);
        } catch (\Exception $e) {
            // return to page not found
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $result = RoleFacade::saveRole($request->all());

            // check if validation errors occured or not
            if ($result instanceof MessageBag) {
                $failureResponse = array(
                    "code" => config('messages.VALIDATION_FAILED.errorCode'),
                    "data" => $result->messages()
                );
                // return the error response to the user
//                 return Helpers::errorResponseHandler('Validation error',
//                     40103,401, $result->errors());
                return Helpers::failureResponseHandler($failureResponse, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            }

            // return the success response to the user
            return Helpers::successResponseHandler('Success', $result);
        } catch (\Exception $e) {
            // return the error response to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Request $request)
    {
        try {
            $role = RoleFacade::getRole($request->get('slug'));
            // return the success response to the user
            return Helpers::successResponseHandler('Success', $role);
        } catch (\Exception $e) {
            // return the error response to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        try {
            $result = RoleFacade::updateRole($request->all());

            // check if validation errors occured or not
            if ($result instanceof \Illuminate\Validation\Validator) {
                // return the error response to the user
                return Helpers::errorResponseHandler('Validation error', 40103, 401, $result->errors());
            }
            // return the success response to the user
            return Helpers::successResponseHandler('Success', $result);
        } catch (\Exception $e) {
            // return the error response to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        try {
            $result = RoleFacade::deleteRole($request->get('slug'));

            // return the success response to the user
            return Helpers::successResponseHandler('Success', $result);
        } catch (\Exception $e) {
            // return the error response to the user
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Display a listing of the role with permissions.
     *
     * @return Response
     */
    public function getRolePermissions()
    {
        try {
            $roles = RoleFacade::getRolePermissions();
            return Helpers::successResponseHandler(['message'=> 'Success','data'=> ['roles' => $roles]]);
        } catch (\Exception $e) {
            // return to page not found
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
    * Function to get role on the basis on permission
    *
    * @param array $request
    * @return $esponse
    */
    public function getRoleByPermission(Request $request)
    {
        try {
            $input = $request->all();
            $roles = RoleFacade::getRoleByPermission($input);
            return Helpers::successResponseHandler(['message'=> 'Success','data'=> ['roles' => $roles]]);
        } catch (\Exception $e) {
            // return to page not found
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
    * Function to now whether the user has access to the given resouce or not
    *
    * @param array $request
    *
    * @return $esponse
    */
    public function getResourceAccessPermission(Request $request)
    {
        try {
            //fetch all the inputs from the request object
            $input = $request->all();

            //check the resource access on the basis of logged-in user id
            $resourceAccessAllowed = RoleFacade::getResourceAccessPermission($input);

            //check if the resource access is allowed to the user or not
            if ($resourceAccessAllowed) {
                //send the success response
                return Helpers::successResponseHandler(['message'=> 'Success','data'=> ['resourceAccessAllowed' => true]]);
            } else {
                //send the failure response
                return Helpers::failureResponseHandler(['data'=>['resourceAccessAllowed' => false]], 403);
            }
        } catch (\Exception $e) {
            // return to page not found
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }
}
