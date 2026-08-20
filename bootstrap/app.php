<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: [
            __DIR__ . '/../routes/api.php',
            __DIR__ . '/../routes/api_v1.php',
        ],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );

        /*STRUCTURE I want
         * {
            errors: [
                {
                    "status": 422,
                    "type": ValidationException,
                    "message": 'validation error message',
                    "source": 'attribute.i' (don't use in prod)
                }
        ]
        }
        */

        // $exceptions->render(function(OtherExceptionTypes $e, Request $request) {});

        $exceptions->render(function (ValidationException $exception) {

            $errors = [];

            foreach ($exception->errors() as $key => $value) {
                foreach ($value as $message) {
                    $errors[] = [
                        'status' => 422,
                        'message' => $message,
                        'source' => $key
                    ];
                }
            }

            return response()->json([
                'errors' => $errors
            ]);
        });

        $exceptions->render(function (NotFoundHttpException $exception) {
            return [
                [
                    'status' => 404,
                    'message' => 'The resource cannot be found',
                ]
            ];
        });

        $exceptions->render(function (AuthenticationException $exception) {
            return [
                [
                    'status' => 403,
                    'message' => 'Unauthenticated',
                ]
            ];
        });

        $exceptions->render(function (Throwable $exception) {

            $className = get_class($exception);
            $index = strrpos($className, '\\');

            return response()->json([
                'errors' => [
                    [
                        'type' => substr($className, $index + 1),
                        'status' => 0,
                        'message' => $exception->getMessage(),
                        //↓ Only for studying purposes ↓
                        'source' => 'Line: ' . $exception->getLine() . ': ' . $exception->getFile()
                    ]
                ]
            ]);
        });

    })->create();
