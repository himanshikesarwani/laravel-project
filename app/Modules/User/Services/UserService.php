<?php
namespace App\Modules\User\Services;

use Auth;
use Validator;
use Illuminate\Http\Request;
use App\Entities\User;
use Exception;
use App\Modules\User\Repositories\User\UserInterface;
use App\Services\BaseService;
use App\Libraries\HelpersFacade as Helpers;
use Illuminate\Support\MessageBag;

class UserService extends BaseService
{

    /*
     * |--------------------------------------------------------------------------
     * | UserService
     * |--------------------------------------------------------------------------
     * | UserService class containing all useful methods for business logic around User
     * |
     */

    /**
     *
     * @var userRepository
     */
    protected $userRepository;

    /**
     *
     * @var unknown
     */
    private $responseData;

    /**
     * Function to set user repository object
     *
     * @param UserInterface $userRepository
     */
    public function __construct(UserInterface $userRepository)
    {
        parent::__construct($userRepository);
        $this->userRepository = $userRepository;
    }

    /**
     * Method to get user authenticate
     *
     * @param array $request
     *
     * @return response
     */
    public function authenticateUser(array $request)
    {
        try {
            // Format and trim the request
            $input = array_map('trim', $request);
            // Set validation rules
            $validator = Validator::make($input, [
                'email' => 'required|email',
                'password' => 'required'
            ]);
          
            // Running the validation
            if (is_object($validator) && ! empty($validator) && $validator->fails()) {
                return $validator;
            }
         
            // call api for login
            $user = $this->userRepository->userAuthenticate($input);
            // Check for valid user
            if ($user instanceof User) { 
                return $user;
            }
            // Authentication failed...
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to check the existence of user email in the system
     *
     * @param string $email
     *
     * @return boolean
     */
    public function checkUserEmailExistence($email)
    {
        try {
            // Creating the validation rules
            $rules = [
                'email' => 'required|email'
            ];

            // creating the validator object with girven rules & values
            $validator = Validator::make([
                'email' => $email
            ], $rules);

            // if validation failed return errors
            if ($validator->fails()) {
                return $validator;
            }

            // check the existence of email in the database
            return $this->userRepository->checkUserEmailExistence($email);
        } catch (\Exception $e) {
            // Some Exception occured, throw it to the the parent function
            throw $e;
        }
    }

    /**
     * Function to get the user account status whether it is locked or unlocked
     *
     * @param string $email
     *
     * @return boolean
     */
    public function getAccountStatus($email)
    {
        try {
            return $this->userRepository->getAccountStatus($email);
        } catch (\Exception $e) {
            // Some Exception occured, throw it to the the parent function
            throw $e;
        }
    }

    /**
     * Function to send password reset email to user
     *
     * @param string $email
     *
     * @return boolean
     */
    public function sendPasswordResetEmailAdmin($email)
    {
        try {
            return $this->userRepository->sendPasswordResetEmailAdmin($email);
        } catch (\Exception $e) {
            // Some Exception occured, throw it to the the parent function
            throw $e;
        }
    }

    /**
     * Function to send resend password code to a mobile number
     *
     * @param string $ums_guid
     *
     * @return boolean
     */
    public function sendResendPasswordCode($inputs)
    {
        try {
            $input = array_map('trim', $inputs);
            // Set validation rules
            $validator = Validator::make($input, [
                'userName' => 'required',
                'phoneNumber' => 'required'
            ]);
            // Running the validation
            if (is_object($validator) && ! empty($validator) && $validator->fails()) {
                return $validator;
            }

            // get user phone number
            $phoneNumber = $this->userRepository->getUserPhoneNumber($input['userName']);

            // verify phone number with input phone number
            if ($phoneNumber == $input['phoneNumber']) {
                return $this->sendResetPasswordCode($input);
            } else {
                // if phone number doesn't match
                return array(
                    'status' => config('api-config.ERROR'),
                    'message' => config('messages.INVALID_PHONENUMBER')
                );
            }
        } catch (\Exception $e) {
            // Some Exception occured, throw it to the the parent function
            throw $e;
        }
    }

    /**
     * Function for calling api of send reset password code
     *
     * @param unknown $input
     * @return mixed[]|\Laravel\Lumen\Application[]
     */
    protected function sendResetPasswordCode($input)
    {
        // call api for getting user details
        $response = $this->userRepository->sendResendPasswordCode($input);

        // getting response from ums
        $response = $response->getUMSResponse();

        // check response and return array for status and message
        if ($response['isApplicationUserAccessAllowed'] == true && $response['isAccessCodeSent'] == true) {
            return array(
                'status' => config('api-config.SUCCESS'),
                'message' => config('messages.PIN_SEND')
            );
        } else if ($response['isApplicationUserAccessAllowed'] == false || $response['isAccessCodeSent'] == false) {
            return array(
                'status' => config('api-config.ERROR'),
                'message' => config('messages.SOME_ERROR_OCCURED')
            );
        }
    }

    /**
     * Function to change user password after first login
     *
     * @params array
     *
     * @return boolean
     */
    public function changePassword($changePasswordRequest)
    {
        try {
            $isRequestParamsValid = $this->validChangePasswordRequest($changePasswordRequest);
            if ($isRequestParamsValid instanceof MessageBag) {
                return $isRequestParamsValid;
            }

            $passwordChangeResponse = $this->userRepository->changePassword($changePasswordRequest);

            // fetch response array from UMS API
            $passwordChangeResponseArray = $passwordChangeResponse->getUMSResponse();

            // determine response of change password
            $umsResponse = new \stdClass();
            $umsResponse->passwordChangeResponse = $passwordChangeResponse;
            $umsResponse->passwordChangeResponseArray = $passwordChangeResponseArray;
            $umsResponse->userName = $changePasswordRequest['userName'];
            $this->responseData = $this->changePasswordResponse($umsResponse);
            return $this->responseData;
        } catch (Exception $exception) {
            // Some Exception occured, throw it to the the parent function
            throw $exception;
        }
    }

    /**
     * Function to verify pin code user enter's
     *
     * @params array
     *
     * @return boolean
     */
    public function verifyPinCode(Array $input)
    {
        try {
            // Creating the validation rules
            $rules = [
                'userName' => 'required',
                'smsCode' => 'required',
                'resetPageUrl' => 'required|url'
            ];

            // Custom messages for validation
            $messages = [
                'userName.required' => config('messages.USER.FORGOT_PASSWORD.EMAIL_REQUIRED'),
                'smsCode.required' => config('messages.VERIFY_CODE.SMS_CODE_REQUIRED'),
                'resetPageUrl.required' => config('messages.VERIFY_CODE.RESET_URL_REQUIRED'),
                'resetPageUrl.url' => config('messages.VERIFY_CODE.RESET_URL_FORMAT')
            ];
            
            // creating the validator object with girven rules & values
            $validator = APIValidator::make($input, $rules, $messages);
            
            // if validation failed return errors
            if ($validator->fails()) {
                return $validator->errors();
            }
            
            // call api for verifying security pin and sending email
            $response = $this->userRepository->verifyPinCode($input);

            // get ums response
            $response = $response->getUMSResponse();

            // check response and set array for status and message
            if (isset($response['isApplicationUserAccessAllowed']) && isset($response['isSMSCodeVerified']) && isset($response['isSMSCodeMatched']) && isset($response['isSMSCodeValid']) && isset($response['isResetEmailSent'])) {
                if ($response['isApplicationUserAccessAllowed'] == true && $response['isSMSCodeVerified'] == true && $response['isSMSCodeMatched'] == true && $response['isSMSCodeValid'] == true && $response['isResetEmailSent'] == true) {
                    return array(
                        'status' => config('api-config.SUCCESS'),
                        'message' => config('messages.Email_SUCCESS')
                    );
                } else {
                    return new APIMessageBag(array(
                        config('messages.INVALID_PIN')
                    ));
                }
            } else {
                return new APIMessageBag(array(
                    config('messages.SOME_ERROR_OCCURED')
                ));
            }
        } catch (\Exception $e) {
            // Some Exception occured, throw it to the the parent function
            throw $e;
        }
    }

    /**
     * Function to save reset password
     *
     * @param array $input
     * @throws Exception
     * @return unknown
     */
    public function saveResetPassword(Array $input)
    {
        try {
            // Password policy regex
            $regex1 = '(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}';
            $regex2 = '(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}';
            $regex3 = '(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}';
            $regex4 = '(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[#?!@$%^&*-]).{8,}';
            $regex5 = '(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}';

            // Set validation rules
            $validationArray = array(
                'new_password' => array(
                    'required',
                    'min:8',
                    'regex:/^(' . $regex1 . '|' . $regex2 . '|' . $regex3 . '|' . $regex4 . '|' . $regex5 . ')$/'
                ),
                'confirm_password' => 'required|same:new_password'
            );

            // Make and return validator object
            $validator = Validator::make($input, $validationArray);
            if (is_object($validator) && ! empty($validator) && $validator->fails()) {
                return $validator->errors();
            }
            // set parameter
            $umsRequest = array(
                'userName' => $input['userName'],
                'hashCode' => $input['hashCode'],
                'newPassword' => $input['new_password']
            );

            // call helper function for triming array
            $umsRequest = Helpers::trimArray($umsRequest);

            // call repository function for saving password
            $resetPasswordResponse = $this->userRepository->saveResetPassword($umsRequest);

            // get ums response
            $resetPasswordArray = $resetPasswordResponse->getUMSResponse();

            $umsResponse = new \stdClass();
            $umsResponse->resetPasswordResponse = $resetPasswordResponse;
            $umsResponse->resetPasswordResponseArray = $resetPasswordArray;

            // fetch response array from UMS API
            $this->responseData = $this->resetPasswordResponse($umsResponse);
            return $this->responseData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to reset password using forgot password method
     *
     * @param array $requestData
     *
     * @throws Exception
     *
     * @return number array
     */
    public function forgotPassword($requestData)
    {
        try {
            // Validating data
            $validationResult = $this->validateForgotPassword($requestData);

            // If validation fails throwing error
            if (isset($validationResult['status']) && $validationResult['status'] == config('api-config.ERROR')) {
                return array(
                    'status' => config('api-config.ERROR'),
                    'errorData' => $validationResult['errorData']
                );
            } else {
                // Fetching user details
                $userData = $this->userRepository->getUserData($requestData['username']);
                $userData->roles = $userData->roles()
                    ->get()
                    ->toArray();
                $userData->permissions = $userData->roles()->get()[0]->permissions()
                    ->get()
                    ->toArray();

                // If user is admin
                if ($userData->can('admin')) {
                    // Check user status
                    $userStatus = $this->userRepository->checkUserStatus($requestData);
                    if (! empty($userStatus) && $userStatus->isUserFound && ! $userStatus->isLocked) {
                        // send an email to the user containing password reset link
                        $emailSent = $this->userRepository->checkEmailSent($requestData);
                        if ($emailSent->isApplicationUserAccessAllowed) {
                            return array(
                                'status' => config('api-config.SUCCESS'),
                                'data' => $userData
                            );
                        } else {
                            // Throwing exception if email is not sent
                            throw new \Exception();
                        }
                    } else {
                        // Throwing error if account is locked
                        return array(
                            'status' => config('api-config.ERROR'),
                            'errorData' => [
                                'email' => config('messages.USER.FORGOT_PASSWORD.ACCOUNT_LOCKED')
                            ]
                        );
                    }
                } else if ($userData->can('gp')) {
                    // Sending user details if user is GP
                    $userData->user_details = $userData->userDetail()->get();
                    return array(
                        'status' => config('api-config.SUCCESS'),
                        'data' => $userData
                    );
                } else {
                    // If no role has been assigned to user
                    return array(
                        'status' => config('api-config.ERROR'),
                        'errorData' => [
                            'user' => config('messages.USER.FORGOT_PASSWORD.NO_ROLE')
                        ]
                    );
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Method to get user details
     *
     * @param array $request
     * @throws Exception
     * @return unknown|\App\Entities\User|boolean
     */
    public function userDetails(array $request)
    {
        try {
            // Format and trim the request
            $input = array_map('trim', $request);
            
            // Set validation array
            $validationArray = array(
                'id' => 'required|numeric'
            );
            // set custom message
            $messages = array(
                'id.required' => config('messages.USER_ID_REQUIRED'),
                'id.numeric' => config('messages.USER_ID_NUMERIC')
            );
            
            $validator = APIValidator::make($input, $validationArray, $messages);
            //check validation
            if ($validator->fails()) {
                return $validator->errors();
            }
             // call api for getting user details
            $userDetails = $this->userRepository->userDetails($input['id']);
           
            // Check for valid user
            if (is_object($userDetails) && (count(get_object_vars($userDetails)) > 0)) {
                return array(
                    'status' => config('api-config.SUCCESS'),
                    'message' => config('messages.RECORD_FOUND'),
                    'data' => $userDetails
                );
            } else {
                return array(
                    'status' => config('api-config.ERROR'),
                    'message' => config('messages.USER.DOES_NOT_EXIST')
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Method to get user details
     *
     * @param array $request
     * @throws Exception
     * @return unknown|\App\Entities\User|boolean
     */
    public function userQualifications(array $request)
    {
        try {
            // Format and trim the request
            $input = array_map('trim', $request);
            
            // Set validation array
            $validationArray = array(
                'id' => 'required|numeric'
            );
            
            // set custom message
            $messages = array(
                'id.required' => config('messages.USER_ID_REQUIRED'),
                'id.numeric' => config('messages.USER_ID_NUMERIC')
            );
            
            $validator = APIValidator::make($input, $validationArray, $messages);
            //check validation
            if ($validator->fails()) {
                return $validator->errors();
            }
            
            // call api for getting user qualifications
            $userQualifications = $this->userRepository->userQualification($input['id']);

            // Check for valid user
            if (count($userQualifications)) {
                return array(
                    'status' => config('api-config.SUCCESS'),
                    'message' => config('messages.RECORD_FOUND'),
                    'data' => $userQualifications
                );
            } else {
                return array(
                    'status' => config('api-config.ERROR'),
                    'message' => config('messages.RECORD_NOT_FOUND')
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to validate forgot password input data
     *
     * @param array $requestData
     *
     * @throws Exception
     *
     * @return number array
     */
    private function validateForgotPassword($requestData)
    {
        // Rules for validation
        $rules = [
            'username' => 'required|email|exists:users,email'
        ];

        // Custom messages for validation
        $messages = [
            'username.required' => config('messages.USER.FORGOT_PASSWORD.EMAIL_REQUIRED'),
            'username.email' => config('messages.USER.FORGOT_PASSWORD.INCORRECT_EMAIL_FORMAT'),
            'username.exists' => config('messages.USER.FORGOT_PASSWORD.EMAIL_NOT_FOUND')
        ];

        $validationResult = APIValidator::make($requestData, $rules, $messages);

        // If validation fails
        if ($validationResult->fails()) {
            return array(
                'status' => config('api-config.ERROR'),
                'errorData' => $validationResult->errors()
            );
        } else {
            return array(
                'status' => config('api-config.SUCCESS')
            );
        }
    }

    /**
     * This function is used to validate change password request.
     *
     * @param array $requestParameters
     * @return \Illuminate\Contracts\Support\MessageBag on error else true
     */
    private function validChangePasswordRequest($requestParameters)
    {
        // Creating the validation rules
        $rulesOldPassword = [
            'userName' => 'required'
        ];

        $rulesNewPassword = Helpers::passwordValidationRule();

        $rules = array_merge($rulesOldPassword, $rulesNewPassword);

        $messages = array(
            'userName.required' => config('messages.CHANGE_PASSWORD.USERNAME_REQUIRED'),
            'newPassword.required' => config('messages.CHANGE_PASSWORD.NEW_REQUIRED'),
            'newPassword.regex' => config('messages.CHANGE_PASSWORD.INCORRECT_FORMAT')
        );

        if (isset($requestParameters['currentPassword'])) {
            array_merge($rulesOldPassword, [
                'currentPassword' => 'required'
            ]);
            array_merge($messages, [
                'currentPassword.required' => config('messages.CHANGE_PASSWORD.OLD_REQUIRED')
            ]);
        } else {
            array_merge($rulesOldPassword, [
                'tempPassword' => 'required'
            ]);
            array_merge($messages, [
                'tempPassword.required' => config('messages.CHANGE_PASSWORD.OLD_REQUIRED')
            ]);
        }

        // creating the validator object with girven rules & values
        $this->validator = Validator::make($requestParameters, $rules, $messages);

        // if validation failed return errors
        if ($this->validator->fails()) {
            return $this->validator->errors();
        } else {
            return true;
        }
    }

    /**
     * Function to determine response of change password.
     *
     * @param object $passwordChangeResponse
     * @param array $passwordChangeResponseArray
     * @return array
     */
    private function changePasswordResponse($umsResponse)
    {
        if ($umsResponse->passwordChangeResponse->getResponseHTTPCode() == config('api-config.SUCCESS_HTTP_CODE')) {
            if (array_key_exists('isPasswordChanged', $umsResponse->passwordChangeResponseArray) && array_key_exists('isCurrentPasswordCorrect', $umsResponse->passwordChangeResponseArray) && array_key_exists('newPasswordValidationResult', $umsResponse->passwordChangeResponseArray)) {
                if ($umsResponse->passwordChangeResponseArray['isPasswordChanged'] == false && $umsResponse->passwordChangeResponseArray['isCurrentPasswordCorrect'] == false) {
                    $this->validator->errors()->add('currentPassword', config('messages.CHANGE_PASSWORD.CURRENT_PASSWORD_NOT_CORRECT'));
                    return $this->validator->errors();
                } else if ($umsResponse->passwordChangeResponseArray['isPasswordChanged'] == false && $umsResponse->passwordChangeResponseArray['newPasswordValidationResult'] != null) {
                    if (isset($umsResponse->passwordChangeResponseArray['newPasswordValidationResult']['isHistoryCheckPassed'])) {
                        if ($umsResponse->passwordChangeResponseArray['newPasswordValidationResult']['isHistoryCheckPassed'] == false) {
                            $this->validator->errors()->add('newPassword', config('messages.CHANGE_PASSWORD.PASSWORD_HISTORY_INVALID'));
                            return $this->validator->errors();
                        }
                    }
                    // if password policy does not match
                    $this->validator->errors()->add('newPassword', config('messages.CHANGE_PASSWORD.INCORRET_PASSWORD_POLICY'));
                    return $this->validator->errors();
                } else if ($umsResponse->passwordChangeResponseArray['isPasswordChanged'] == true && $umsResponse->passwordChangeResponseArray['isCurrentPasswordCorrect'] == true && $umsResponse->passwordChangeResponseArray['newPasswordValidationResult'] != null) {
                    $this->userRepository->updateFirstLoginCheck($umsResponse->userName);
                    return array(
                        'status' => config('api-config.SUCCESS'),
                        'message' => config('messages.CHANGE_PASSWORD.SUCCESS')
                    );
                }
            }
        } else {
            // if any other error occured
            $this->validator->errors()->add('password', config('messages.SOME_ERROR_OCCURED'));
            return $this->validator->errors();
        }
    }

    /**
     * Function to determine response of reset password.
     *
     * @param object $passwordChangeResponse
     * @param array $passwordChangeResponseArray
     * @return array
     */
    private function resetPasswordResponse($umsResponse)
    {
        if ($umsResponse->resetPasswordResponse->getResponseHTTPCode() == config('api-config.SUCCESS_HTTP_CODE')) {
            if (array_key_exists('isApplicationUserAccessAllowed', $umsResponse->resetPasswordResponseArray) && array_key_exists('isPasswordChanged', $umsResponse->resetPasswordResponseArray) && array_key_exists('isEmailCodeVerified', $umsResponse->resetPasswordResponseArray) && array_key_exists('isEmailCodeMatched', $umsResponse->resetPasswordResponseArray) && array_key_exists('isEmailCodeValid', $umsResponse->resetPasswordResponseArray) && array_key_exists('newPasswordValidationResult', $umsResponse->resetPasswordResponseArray)) {
                if ($umsResponse->resetPasswordResponseArray['isApplicationUserAccessAllowed'] == false && $umsResponse->resetPasswordResponseArray['isPasswordChanged'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeVerified'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeMatched'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeValid'] == false) {
                    return array(
                        'status' => config('api-config.ERROR'),
                        'message' => config('messages.RESET_PASSWORD.USERNAME_OR_HASHCODE_INCORRECT')
                    );
                } else if ($umsResponse->resetPasswordResponseArray['isApplicationUserAccessAllowed'] == true && $umsResponse->resetPasswordResponseArray['isPasswordChanged'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeVerified'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeMatched'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeValid'] == false && $umsResponse->resetPasswordResponseArray['isEmailCodeUsed'] == true) {
                    return array(
                        'status' => config('api-config.ERROR'),
                        'message' => config('messages.RESET_PASSWORD.HASHCODE_ALREADY_USED')
                    );
                } else if ($umsResponse->resetPasswordResponseArray['newPasswordValidationResult'] == null) {
                    return array(
                        'status' => config('api-config.ERROR'),
                        'message' => config('messages.RESET_PASSWORD.INVALID_PASSWORD')
                    );
                } else if ($umsResponse->resetPasswordResponseArray['isApplicationUserAccessAllowed'] == true && $umsResponse->resetPasswordResponseArray['isEmailCodeVerified'] == true && $umsResponse->resetPasswordResponseArray['isEmailCodeMatched'] == true && $umsResponse->resetPasswordResponseArray['isEmailCodeValid'] == true && $umsResponse->resetPasswordResponseArray['newPasswordValidationResult'] != null) {
                    if (isset($umsResponse->resetPasswordResponseArray['newPasswordValidationResult']['isHistoryCheckPassed'])) {
                        if ($umsResponse->resetPasswordResponseArray['newPasswordValidationResult']['isHistoryCheckPassed'] == false) {
                            return array(
                                'status' => config('api-config.ERROR'),
                                'message' => config('messages.CHANGE_PASSWORD.PASSWORD_HISTORY_INVALID')
                            );
                        } else if ($umsResponse->resetPasswordResponseArray['isPasswordChanged'] == true) {
                            return array(
                                'status' => config('api-config.SUCCESS'),
                                'message' => config('messages.RESET_PASSWORD.SUCCESS')
                            );
                        } else {
                            return array(
                                'status' => config('api-config.ERROR'),
                                'message' => config('messages.RESET_PASSWORD.PASSWORD_NOT_CHANGE')
                            );
                        }
                    }
                } else {
                    return array(
                        'status' => config('api-config.ERROR'),
                        'message' => config('messages.RESET_PASSWORD.PASSWORD_NOT_CHANGE')
                    );
                }
            }
        } else {
            return array(
                'status' => config('api-config.ERROR'),
                'message' => config('messages.SOME_ERROR_OCCURED')
            );
        }
    }

    /**
     * Function to map user object to set UMS data
     *
     * @param User $user
     * @param Response $response
     */
    private function mapUserAttribute(User &$user, $response)
    {
        $user->isUserFound = isset($response['isUserFound']) ? $response['isUserFound'] : '';
        $user->isAuthenticated = isset($response['isAuthenticated']) ? $response['isAuthenticated'] : '';
        $user->isEnabled = isset($response['isEnabled']) ? $response['isEnabled'] : '';
        $user->isLocked = isset($response['isLocked']) ? $response['isLocked'] : '';
        $user->isApplicationUserAccessAllowed = isset($response['isApplicationUserAccessAllowed']) ? $response['isApplicationUserAccessAllowed'] : '';
        $user->passwordExpiryDays = isset($response['passwordExpiryDays']) ? $response['passwordExpiryDays'] : '';
        $user->isSystemGeneratedPassword = isset($response['isSystemGeneratedPassword']) ? $response['isSystemGeneratedPassword'] : '';
        $user->userName = isset($response['userName']) ? $response['userName'] : '';
        $user->dbSessionId = isset($response['dbSessionId']) ? $response['dbSessionId'] : '';
    }

    /**
     * Function to get active experts list
     *
     * @throws Exception
     *
     * @return object
     */
    public function getExperts($permissions)
    {
        try {
            // call api for getting active experts list
            $experts = $this->userRepository->getActiveExperts($permissions);
            return $experts;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Function to get salutations
     *
     * @throws Exception
     *
     * @return object
     */
    public function getSalutations()
    {
        try {
            // get all salutations
            $salutations = $this->userRepository->getSalutations();
            return $salutations;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
