<?php

namespace App\Services;

use App\Enums\ResponseCode;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * 註冊用戶
     */
    public function register(array $data)
    {
        // 檢查 Email 是否已存在
        if ($this->userRepository->findByEmail($data['email'])) {
            return [
                'code' => ResponseCode::REGISTER_EMAIL_EXISTS,
            ];
        }

        // 創建用戶
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!$user) {
            return [
                'code' => ResponseCode::REGISTER_FAIL,
            ];
        } else {
            return [
                'code' => ResponseCode::REGISTER_SUCCESS,
            ];
        }
    }

    /**
     * 用戶登出
     */
    public function logout()
    {
        $user = Auth::user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
            return [
                'code' => ResponseCode::LOGOUT_SUCCESS,
            ];
        }

        return [
            'code' => ResponseCode::LOGOUT_FAIL,
        ];
    }

    /**
     * 用戶登錄
     */
    public function login(array $credentials)
    {
        // 檢查用戶是否存在
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            return [
                'code' => ResponseCode::LOGIN_USER_NOT_FOUND,
            ];
        }

        // 驗證密碼
        if (!Hash::check($credentials['password'], $user->password)) {
            return [
                'code' => ResponseCode::LOGIN_INVALID_PASSWORD,
            ];
        }

        // 獲取當前用戶
        $expirationMinutes = config('sanctum.expiration');
        $userAgent = request()->header('User-Agent');
        $ipAddress = request()->ip();
        $deviceType = $this->detectDeviceType($userAgent);

        // 檢查並清理過期的 token
        $this->cleanupExpiredTokens($user);

        // 檢查該設備是否已有 token
        $existingToken = $user->tokens()
            ->where('device_info', $userAgent)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($existingToken) {
            // 如果存在，則更新現有 token
            $existingToken->delete();
        }

        // 檢查 token 數量限制
        $maxTokens = config('sanctum.max_tokens', 5); // 設置最大 token 數量
        if ($user->tokens()->count() >= $maxTokens) {
            // 刪除最舊的 token
            $user->tokens()->oldest()->first()->delete();
        }

        // 創建新 Token
        $tokenResult = $user->createToken("{$deviceType}-API_Token");
        $tokenModel = $tokenResult->accessToken;
        $tokenModel->expires_at = now()->addMinutes($expirationMinutes);
        $tokenModel->device_info = $userAgent;
        $tokenModel->ip_address = $ipAddress;
        $tokenModel->save();

        return [
            'code' => ResponseCode::LOGIN_SUCCESS,
            'data' => [
                'token' => $tokenResult->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ];
    }

    /**
     * 清理過期 token
     *
     * @param User $user
     * @return void
     */
    private function cleanupExpiredTokens($user)
    {
        $user->tokens()
            ->where('expires_at', '<', now())
            ->delete();
    }

    /**
     * 檢測裝置類型
     */
    private function detectDeviceType($userAgent)
    {
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/windows|macintosh|linux/i', $userAgent)) {
            return 'PC';
        } else {
            return 'Unknown';
        }
    }

    /**
     * 獲取用戶現有的活躍 Token
     */
    private function getExistingActiveToken($user)
    {
        return $user->tokens()
        ->where('tokenable_id', $user->id)
        ->where('expires_at', '>', now())
        ->exists();
    }
}
