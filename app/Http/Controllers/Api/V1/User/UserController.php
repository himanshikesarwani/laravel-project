<?php
namespace App\Http\Controllers\Api\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modules\User\Services\Facades\UserFacade as User;
use Exception;
use Illuminate\Contracts\View\View;
use App\Modules\User\Services\Facades\UserFacade;
use Illuminate\Validation\Validator;
use Illuminate\Routing\Controller as BaseController;
use App\Libraries\HelpersFacade as Helpers;
use JWTAuth;
use Illuminate\Support\MessageBag;

class UserController extends BaseController
{

    /*
     * |--------------------------------------------------------------------------
     * | UserController
     * |--------------------------------------------------------------------------
     * |
     * | Here is where you can add, edit, delete and list user and authenticate the user
     * |
     */

    private $responseData;

     /**
     * Function to authenticate the user
     *
     * @param Request $request
     *
     * @return Response
     */
    public function authenticate(Request $request)
    {
        // Authenticate the user via user service
        try {
            $result = User::authenticateUser($request->all());
           
            if ($result instanceof \Illuminate\Validation\Validator) {
                return Helpers::failureResponseHandler(['message'=>'Username and password required'], 404);
            } elseif ($result instanceof \App\Entities\User) {
                $result->authToken = JWTAuth::fromUser($result);
                $result->encryptSalt = uniqid();
                return Helpers::successResponseHandler(['message'=> 'Success','data'=> ['user' => $result]]);
            } else {
                return Helpers::failureResponseHandler(['message'=>'Login credential are wrong'], 404);
            }
        } catch (Exception $e) {
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Forgot password
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/v1/api/portal/user/forgotPassword",
     *     description="Create GP User",
     *     operationId="create",
     *     produces={"application/json"},
     *     tags={"User"},
     *          @SWG\Parameter(
     *             name="body",
     *             in="body",
     *             description="Create user parameters",
     *             type = "json",
     *             default = "{
     ""username"": ""developer0933@mailinator.com"",
     ""resetPageUrl"": ""http://reset-password-url.com?email=developer0933@mailinator.com""
     }",
     *             required=true,
     *     @SWG\Schema(
     *     @SWG\Property(
     *         property = "username",
     *         type = "string",
     *         default = "developer0933@mailinator.com",
     *         description = "user email"
     *      ),
     *     @SWG\Property(
     *         property = "resetPageUrl",
     *         type = "string",
     *         default = "http://reset-password-url.com?email=developer0933@mailinator.com",
     *         description = "password reset url"
     *      )
     *    )
     *    ),
     *     @SWG\Response(
     *         response=200,
     *         description="Passwrod reset successfull",
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Error in forgot password",
     *         @SWG\Schema(
     *                 @SWG\Property(
     *                     property = "status",
     *                     type = "string",
     *                     default = "fail",
     *                     description = "Error occured while forgot password steps"
     *                 ),
     *                 @SWG\Property(
     *                     property = "message",
     *                     type = "string",
     *                     default = "Validation failed",
     *                     description = "error message"
     *                 ),
     *                  @SWG\Property(
     *                     property="data",
     *                     description = "user qualifications",
     *                          @SWG\Property(
     *                          property = "username",
     *                          type = "array",
     *                          @SWG\Items(
     *                              @SWG\Property(property="errorCode", type="integer", default="40121"),
     *                              @SWG\Property(property="message", type="string", default="Username not found")
     *                          )
     *                       ),
     *                  )
     *              )
     *          )
     *      )
     */
    public function forgotPassword(Request $request)
    {
        try {
            $requestData = $request->all();
            $serviceResponse = User::forgotPassword($requestData);

            //Cheking response from service
            if (isset($serviceResponse['status']) && $serviceResponse['status'] == config('api-config.SUCCESS')) {
                return Helpers::successResponseHandler(['message'=> config('messages.FORGOT_PASSWORD_SUCCESS'),'data'=> ['user' => $serviceResponse['data']]]);
            } else {
                return Helpers::failureResponseHandler(['data'=> $serviceResponse['errorData']], 402);
            }
        } catch (Exception $e) {
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Function to redirect to success page after sending reset password link
     *
     * @return view
     */
    public function emailSendSuccess(Request $request)
    {
        $email= $request->get('email');

        if (isset($email) && !empty($email)) {
            return Helpers::successResponseHandler('Success', ['email' => $email]);
        }

        return Helpers::errorResponseHandler('validation error', 40103, 401);
    }

    /**
    * Function to send security pin
    *
    * @return Response
    */
    public function sendResendPasswordCode(Request $request)
    {
        try {
            // get inputs from request
            $inputs = $request->all();

            //send verification pin
            $response= User::sendResendPasswordCode($inputs);

            // check response and verify its status code
            if ($response instanceof \Illuminate\Validation\Validator) {
                $this->responseData= array(
                    'code'=>config('messages.VALIDATION_FAILED.errorCode'),
                    'data' => $response->messages()
                );
                // return failure response
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            } else if ($response['status'] == config('api-config.ERROR')) {
                $this->responseData = array(
                    'code'=>config('api-config.FAILURE_CODE'),
                    'data' => Helpers::formatCustomFailData('mobile', $response['message'])
                );
                // return failure response
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            } else if ($response['status'] == config('api-config.SUCCESS')) {
                $this->responseData = array(
                    'code'=>config('api-config.SUCCESS_CODE'),
                    'data' => (object) $inputs
                );
                // return success response
                return Helpers::successResponseHandler($this->responseData);
            }
        } catch (Exception $e) {
            //return error exception
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Function to change password after first login attempt
     *
     * @params $request object
     * @returns boolean
     */
    public function changePassword(Request $request)
    {
        try {
            // call to save reset password function
            $changePasswordResponse = UserFacade::changePassword($request->all());

            // check out validator instance
            if ($changePasswordResponse instanceof MessageBag) {
                $this->responseData= array(
                    'code'=>config('api-config.FAILURE_CODE'),
                    'data' => $changePasswordResponse
                );
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            } else if ($changePasswordResponse['status'] == config('api-config.SUCCESS')) {
                $this->responseData = array(
                    'code'=>config('api-config.SUCCESS_CODE'),
                    'message'=> $changePasswordResponse['message'],
                    'data'=>(object) array()
                );
                return Helpers::successResponseHandler($this->responseData);
            } else {
                return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
            }
        } catch (Exception $exception) {
            //return error exception
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
    * Function to verify pin code entered by user
    *
    * @param array $request
    * @return view
    */
    public function verifyPinCode(Request $request)
    {
        try {
            // get inputs from request
            $inputs = $request->all();

            //check to send verification pin code
            $verify = User::verifyPinCode($inputs);
          
            // check status and set response
            if ($verify instanceof APIMessageBag) {
                $this->responseData= array(
                    'code'=>config('messages.VALIDATION_FAILED.errorCode'),
                    'data' => $verify
                );
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            } else if ($verify['status'] == config('api-config.SUCCESS')) {
                $this->responseData = array(
                    'code'=>config('api-config.SUCCESS_CODE'),
                    'data' => (object) $inputs
                );
                // return success response
                return Helpers::successResponseHandler($this->responseData);
            }
        } catch (Exception $e) {
            //return error exception
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Function to save reset password
     *
     * @returns view
     */
    public function resetPassword(Request $request)
    {
        try {
            // get inputs from request
            $inputs = $request->all();

            // call to save reset password function
            $response = UserFacade::saveResetPassword($inputs);
            //$response['message']['data'] = $inputs;

            // check status and set response
            if ($response instanceof MessageBag) {
                $this->responseData= array(
                    'code'=>config('messages.VALIDATION_FAILED.errorCode'),
                    'data' => $response->messages()
                );
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            } else if ($response['status'] == config('api-config.ERROR')) {
                $this->responseData = array(
                    'code'=>config('api-config.FAILURE_CODE'),
                    'data' => Helpers::formatCustomFailData('reset-password', $response['message'])
                );
                // return response
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            } else if ($response['status'] == config('api-config.SUCCESS')) {
                $this->responseData = array(
                    'code'=>config('api-config.SUCCESS_CODE'),
                    'data' => (object) $response
                );
                // return success response
                return Helpers::successResponseHandler($this->responseData);
            }
        } catch (\Exception $e) {
            //return error exception
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Function to get User details
     *
     * @param Request $request
     * @return unknown
     */
    public function getUserDetails(Request $request)
    {
        try {
            $details = User::userDetails($request->all());
            $qualification = User::userQualifications($request->all());
            
            // check for status get from service
            if ($details instanceof APIMessageBag) {
                $this->responseData = array(
                    'code' => config('api-config.FAILURE_CODE'),
                    'data' => $details
                );
                return Helpers::failureResponseHandler($this->responseData, 402);
            } elseif ($details['status'] == config('api-config.SUCCESS')) {
                $details = $details['data'];
                $qualification = isset($qualification['data']) ? $qualification['data'] : '';
                $result = new \stdClass();
                $result->userDetails = isset($details) && !empty($details) ? $details->toArray() : '';
                $result->qualifications = isset($qualification) && !empty($qualification) ? $qualification->toArray() : '';
                return Helpers::successResponseHandler(['message'=> 'Success','data'=> $result]);
            } elseif ($details['status'] == config('api-config.ERROR')) {
                $this->responseData = array(
                    'code' => config('api-config.FAILURE_CODE'),
                    'data' => Helpers::formatCustomFailData('userDetails', $details['message'])
                );
                // return error response
                return Helpers::failureResponseHandler($this->responseData, config('api-config.UNACCETABLE_DATA_HTTP_CODE'));
            }
        } catch (Exception $e) {
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }

    /**
     * Function to get Salutations
     *
     * @return json
     */
    public function getSalutations()
    {
        try {
            $salutations= User::getSalutations();
            return Helpers::successResponseHandler(['message'=> 'Success','data'=> $salutations]);
        } catch (Exception $e) {
            return Helpers::errorResponseHandler(config('messages.API_NOT_WORKING.message'));
        }
    }
}
