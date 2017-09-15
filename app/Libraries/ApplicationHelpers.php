<?php
namespace App\Libraries;

use Illuminate\Support\Facades\App;
use App\Libraries\APIResponse\FailureResponse;
use App\Libraries\APIResponse\SuccessResponse;
use App\Libraries\APIResponse\ErrorResponse;

/**
 * Class for creating common helper functions
 */
class ApplicationHelpers
{

    /*
     * Set user data for response
     */
    private $responseData = array();

    /**
     * Common function to return error response
     *
     * @param string $errorMessage
     * @param int $errorCode
     */
    public function errorResponseHandler($errorMessage)
    {
        $errorResponseObj = new ErrorResponse();
        $errorResponseObj->status = 'error';
        $errorResponseObj->code = config('application-config.EXCEPTION_CODE');
        $errorResponseObj->message = $errorMessage;
        return response()->json($errorResponseObj, config('application-config.EXCEPTION_HTTP_CODE'));
    }

    /**
     * Function to return failure response
     *
     * @param unknown $failureResponse
     * @param unknown $HTTPCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function failureResponseHandler($failureResponse, $HTTPCode)
    {
        $failureResponseObject = new FailureResponse();
        $failureResponseObject->code = config('application-config.ERROR_CODE');
        $failureResponseObject->status = config('application-config.ERROR_STATUS');
        $failureResponseObject->message = config('messages.VALIDATION_FAILED.message');
        $failureResponseObject->fill($failureResponse);
        return response()->json($failureResponseObject, $HTTPCode);
    }

    /**
     * Common function to return success response.
     *
     * @param array $successMessage
     *
     * @return $httpCode
     */
    public function successResponseHandler($successResponse)
    {
        $successResponseObj = new SuccessResponse();
        $successResponseObj->status = config('application-config.SUCCESS_STATUS');
        $successResponseObj->fill($successResponse);
        return response()->json($successResponseObj);
    }

    /**
     * Common function to return success response.
     *
     * @param array $successMessage
     *
     * @return $httpCode
     */
    public function objectResponseHandler($responseObj)
    {
        return response()->json($responseObj);
    }
}
