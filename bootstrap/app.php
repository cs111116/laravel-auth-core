<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LogRequests;
use App\Http\Middleware\CheckMaintenance;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Enums\ResponseCode;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(LogRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e, Request $request) {
            // 记录所有异常的详细信息
            $method = debug_backtrace()[1]['function'] ?? 'Unknown function';
            \Log::error('Exception occurred', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'method' => $method,
            ]);

            // 根据异常类型返回相应的 JSON 错误响应
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'fail',
                    'code' => ResponseCode::INVALID_CREDENTIALS_ERROR,
                    'message' => ResponseCode::getErrorDetails(ResponseCode::INVALID_CREDENTIALS_ERROR)['title'],
                ], 401);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'status' => 'fail',
                    'code' => ResponseCode::RESOURCE_NOT_FOUND,
                    'message' => ResponseCode::getErrorDetails(ResponseCode::RESOURCE_NOT_FOUND)['title'],
                ], 404);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => 'fail',
                    'code' => ResponseCode::ROUTE_NOT_FOUND,
                    'message' => ResponseCode::getErrorDetails(ResponseCode::ROUTE_NOT_FOUND)['title'],
                ], 404);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'fail',
                    'code' => ResponseCode::VALIDATION_ERROR,
                    'message' => ResponseCode::getErrorDetails(ResponseCode::VALIDATION_ERROR)['title'],
                    // 'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof QueryException) {
                return response()->json([
                    'status' => 'error',
                    'code' => ResponseCode::DATABASE_ERROR,
                    'message' => '伺服器內部錯誤，請稍後重試。',
                ], 500);
            }

            // 默认返回服务器错误
            return response()->json([
                'status' => 'error',
                'code' => ResponseCode::SERVER_ERROR,
                'message' => ResponseCode::getErrorDetails(ResponseCode::SERVER_ERROR)['title'],
            ], 500);
        });
    })->create();
