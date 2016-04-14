<?php

namespace Northstar\Exceptions;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException as LegacyValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Psr\Http\Message\ResponseInterface;
use Exception;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        OAuthServerException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        LegacyValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        // If we receive a OAuth exception, get the included PSR-7 response,
        // convert it to a standard Symfony HttpFoundation response and return.
        if ($e instanceof OAuthServerException) {
            $psrResponse = $e->generateHttpResponse(app(ResponseInterface::class));

            return (new HttpFoundationFactory())->createResponse($psrResponse);
        }

        // If client requests it, render exception as JSON object
        if ($request->ajax() || $request->wantsJson()) {

            // If reporting a validation exception, use the prepared response
            // @see \Northstar\Http\Controller@buildFailedValidationResponse
            if ($e instanceof ValidationException) {
                return $e->response;
            }

            $code = 500;
            if ($this->isHttpException($e)) {
                $code = $e->getStatusCode();
            }

            $response = [
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ],
            ];

            // Show more information if we're in debug mode
            if (config('app.debug')) {
                $response['debug'] = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return response()->json($response, $code);
        }

        return parent::render($request, $e);
    }
}
