<?php

namespace Northstar\Exceptions;

use Illuminate\Http\JsonResponse;
use Exception;

class NorthstarValidationException extends Exception
{
    /**
     * The errors from the validator.
     *
     * @var array
     */
    protected $errors;

    /**
     * The formatted response for this exception.
     *
     * @var array
     */
    protected $response;

    /**
     * Create a new exception instance.
     *
     * @param array $errors
     */
    public function __construct($errors)
    {
        parent::__construct('The given data failed to pass validation.');

        $this->errors = $errors;
        $this->response = [
            'error' => [
                'code' => 422,
                'message' => 'Failed validation.',
                'fields' => $errors,
            ],
        ];
    }

    /**
     * Get the key-value array of errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the formatted JSON response.
     *
     * @return JsonResponse
     */
    public function getResponse()
    {
        return new JsonResponse($this->response, 422);
    }
}
