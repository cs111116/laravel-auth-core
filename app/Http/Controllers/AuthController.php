<?php

namespace App\Http\Controllers;

use App\Requests\LoginRequest;
use App\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Services\CaptchaService;
use App\Enums\ResponseCode;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="認證相關 API"
 * )
 */
class AuthController extends Controller
{
    protected $authService;
    protected $captchaService;

    public function __construct(AuthService $authService, CaptchaService $captchaService)
    {
        $this->authService = $authService;
        $this->captchaService = $captchaService;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="用戶註冊",
     *     description="註冊新用戶",
     *     operationId="register",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="用戶名稱"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com", description="電子郵件"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="密碼"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="確認密碼"),
     *             @OA\Property(property="captcha_token", type="string", description="驗證碼 token（如果啟用驗證碼）")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="註冊成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="註冊成功")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="註冊失敗",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="註冊失敗"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="此電子郵件已被註冊")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="驗證失敗",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="驗證失敗"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="請輸入有效的電子郵件地址")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="伺服器錯誤",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="伺服器錯誤")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        try {
            // 驗證驗證碼
            if (env('CAPTCHA_ENABLED', false)) {
                $captchaToken = $request->input('captcha_token');
                if (!$this->captchaService->verify($captchaToken)) {
                    return response()->json([
                        'status' => 'fail',
                        'code' => ResponseCode::CAPTCHA_INVALID,
                        'message' => ResponseCode::getErrorDetails(ResponseCode::CAPTCHA_INVALID)['title'],
                    ], 400);
                }
            }
            $data = $request->validated();

            $result = $this->authService->register($data);
            $httpStatus = match ($result['code']) {
                ResponseCode::REGISTER_SUCCESS => 201,
                ResponseCode::REGISTER_EMAIL_EXISTS => 400,
                default => 400,
            };
            $response = [
                'status' => $httpStatus === 201 ? 'success' : 'fail',
                'code' => $result['code'],
                'message' => ResponseCode::getErrorDetails($result['code'])['title'],
            ];

            return response()->json($response, $httpStatus);
        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'code' => ResponseCode::REGISTER_FAIL,
                'message' => ResponseCode::getErrorDetails(ResponseCode::REGISTER_FAIL)['title'],
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="用戶登入",
     *     description="用戶登入並獲取認證 token",
     *     operationId="login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com", description="電子郵件"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="密碼"),
     *             @OA\Property(property="captcha_token", type="string", description="驗證碼 token（如果啟用驗證碼）")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登入成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="登入成功"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="1|abcdef123456..."),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="登入失敗",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="登入失敗"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="帳號或密碼錯誤")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="驗證失敗",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="驗證失敗"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="請輸入有效的電子郵件地址")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="伺服器錯誤",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="伺服器錯誤")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            // 驗證驗證碼
            if (env('CAPTCHA_ENABLED', false)) {
                $captchaToken = $request->input('captcha_token');
                if (!$this->captchaService->verify($captchaToken)) {
                    return response()->json([
                        'status' => 'fail',
                        'code' => ResponseCode::CAPTCHA_INVALID,
                        'message' => ResponseCode::getErrorDetails(ResponseCode::CAPTCHA_INVALID)['title'],
                    ], 400);
                }
            }

            // 調用服務層登錄邏輯
            $result = $this->authService->login($request->validated());

            $httpStatus = match ($result['code']) {
                ResponseCode::LOGIN_SUCCESS => 200,
                ResponseCode::LOGIN_DUPLICATE_SESSION => 401,
                ResponseCode::LOGIN_INVALID_PASSWORD => 401,
                ResponseCode::LOGIN_USER_NOT_FOUND => 401,
                default => 500,
            };
            $response = [
                'status' => $httpStatus === 200 ? 'success' : 'fail',
                'code' => $result['code'],
                'message' => ResponseCode::getErrorDetails($result['code'])['title'],
            ];
            // 只有成功時回傳 data
            if ($httpStatus === 200 && isset($result['data'])) {
                $response['data'] = $result['data'];
            }
            return response()->json($response, $httpStatus);
        } catch (\Exception $e) {
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'code' => ResponseCode::LOGIN_FAIL,
                'message' => ResponseCode::getErrorDetails(ResponseCode::LOGIN_FAIL)['title'],
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="用戶登出",
     *     description="登出當前用戶並使 token 失效",
     *     operationId="logout",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="登出成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="登出成功")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="未授權",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="未授權")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="登出失敗",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="登出失敗")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="伺服器錯誤",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="伺服器錯誤")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            // 調用服務層登出邏輯
            $result = $this->authService->logout();

            $httpStatus = match ($result['code']) {
                ResponseCode::LOGOUT_SUCCESS => 200,
                ResponseCode::LOGOUT_FAIL => 400,
                default => 500,
            };

            $response = [
                'status' => $httpStatus === 200 ? 'success' : 'fail',
                'code' => $result['code'],
                'message' => ResponseCode::getErrorDetails($result['code'])['title'],
            ];

            return response()->json($response, $httpStatus);
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'code' => ResponseCode::LOGOUT_FAIL,
                'message' => ResponseCode::getErrorDetails(ResponseCode::LOGOUT_FAIL)['title'],
            ], 500);
        }
    }
}
