<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

     /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }



    public function render($request, Throwable $exception)
    {
        
         if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {

            return response()->json([
                'error' => 'Invalid Method. '. $exception->getMessage(),
                'status' => 422
            ], 422);
        }
        
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
              parent::report($exception);
            //$this->convertValidationExceptionToResponse($exception, $request);
        }
        
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {

            return response()->json([
                'error' => 'You dont have the authorization to make this request',
                'status' => 422
            ], 422);
        }
        
        
         if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {

            return response()->json([
                'error' => 'No query results found',
                'status' => 422
            ], 422);
        }
        
        if ($exception instanceof NotFoundHttpException) {

            return response()->json([
                'error' => 'Checky your route, something went wrong'.$exception->getMessage(),
                'status' => 422
            ], 422);
        }
        
        
        return parent::render($request, $exception);
        
    }


    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
