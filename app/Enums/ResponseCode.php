<?php

namespace App\Enums;

/**
 * 定義 API 響應代碼
 * Enums（枚舉）是一種編程結構，用於表示一組固定的常量值集合。這些常量通常具有語義相關性，並且可以在代碼中使用清晰的名稱來替代具體的值（如字符串或數字）。
 * 它的作用是提升代碼的可讀性、可維護性以及減少魔術字符串（Magic Strings）的使用。
 * 在 PHP 中，Enums 自 PHP 8.1 起被原生支持；不過在 PHP 8.1 之前，類似的功能通常由常量類（如 class ResponseCode）實現。
 */
#RESTful
class ResponseCode
{
    #操作成功
    public const OPERATION_SUCCESS = 2000;
    public const CREATE_SUCCESS  = 2001;
    public const NEEDS_CONFIRMATION  = 2100;
    // Login related (範圍: 1000-1099)
    public const LOGIN_SUCCESS = 1000;
    public const LOGIN_INVALID_PASSWORD = 1001;
    public const LOGIN_USER_NOT_FOUND = 1002;
    public const LOGIN_DUPLICATE_SESSION = 1003;
    public const LOGIN_FAIL = 1004;
    // Logout related (範圍: 1100-1199)
    public const LOGOUT_SUCCESS = 1100;
    public const LOGOUT_FAIL = 1101;
    // Register related (範圍: 1200-1299)
    public const REGISTER_SUCCESS = 1200;
    public const REGISTER_EMAIL_EXISTS = 1201;
    public const REGISTER_FAIL = 1202;
    // Captcha related (範圍: 1400-1499)
    public const CAPTCHA_INVALID = 1400;
    public const CAPTCHA_EXPIRED = 1401;
    #CRUD失敗
    public const OPERATION_FAIL = 4000;
    // Invalid Credentials  errors (範圍: 4100-4199)
    public const INVALID_CREDENTIALS_ERROR = 4100;
    // Validation error (範圍: 4200-4299)
    public const VALIDATION_ERROR = 4220;
    // Database-related errors (範圍: 4300-4399)
    #系統錯誤
    public const SERVER_ERROR = 5000;
    public const SERVER_VENDOR_ERROR = 5001;
    public const RESOURCE_NOT_FOUND = 5002;
    public const ROUTE_NOT_FOUND = 5003;
    public const DATABASE_ERROR = 5004;
    public const RESPONSE_CODE_TITLE_ERROR = 5005;
    public const ERROR_DETAILS = [
        self::OPERATION_SUCCESS => [
            'title' => '操作成功',
            'detail' => '用戶操作成功',
        ],
        self::CREATE_SUCCESS => [
            'title' => '新增成功',
            'detail' => '資源已成功建立。',
        ],
        self::NEEDS_CONFIRMATION => [
            'title' => '需要使用者的確認',
            'detail' => '需要使用者的確認',
        ],
        self::LOGIN_SUCCESS => [
            'title' => '登入成功',
            'detail' => '用戶已成功登入系統。',
        ],
        self::LOGIN_INVALID_PASSWORD => [
            'title' => '密碼錯誤',
            'detail' => '您提供的密碼不正確，請重新嘗試。',
        ],
        self::LOGIN_USER_NOT_FOUND => [
            'title' => '帳號不存在',
            'detail' => '未找到與提供的憑據匹配的用戶。',
        ],
        self::LOGIN_DUPLICATE_SESSION => [
            'title' => '重複登入會話',
            'detail' => '該帳號已在其他設備上登入，請確認是否為您本人操作。',
        ],
        self::LOGOUT_SUCCESS => [
            'title' => '登出成功',
            'detail' => '用戶已成功登出系統。',
        ],
        self::LOGOUT_FAIL => [
            'title' => '登出失敗',
            'detail' => '由於意外錯誤，用戶登出失敗。',
        ],
        self::REGISTER_SUCCESS => [
            'title' => '註冊成功',
            'detail' => '用戶註冊已成功完成。',
        ],
        self::REGISTER_EMAIL_EXISTS => [
            'title' => 'Email 已存在',
            'detail' => '該 Email 地址已被註冊，請嘗試其他地址。',
        ],
        self::REGISTER_FAIL => [
            'title' => '註冊失敗',
            'detail' => '由於意外錯誤，用戶註冊失敗。',
        ],
        self::CAPTCHA_INVALID => [
            'title' => '驗證碼無效',
            'detail' => '您提供的驗證碼無效，請重新嘗試。',
        ],
        self::CAPTCHA_EXPIRED => [
            'title' => '驗證碼已過期',
            'detail' => '驗證碼已過期，請刷新後重試。',
        ],
        self::SERVER_ERROR => [
            'title' => '伺服器錯誤',
            'detail' => '發生了意外的伺服器錯誤，請稍後重試。',
        ],
        self::SERVER_VENDOR_ERROR => [
            'title' => '伺服器錯誤',
            'detail' => 'Vendor發生錯誤,請檢查Vendor',
        ],
        self::RESOURCE_NOT_FOUND => [
            'title' => '找不到資源',
            'detail' => '伺服器未找到您請求的資源。',
        ],
        self::ROUTE_NOT_FOUND => [
            'title' => '路由不存在',
            'detail' => '您請求的路由不存在或無效。',
        ],
        self::VALIDATION_ERROR => [
            'title' => '參數驗證失敗',
            'detail' => '您的請求參數驗證未通過，請修正後重試。',
        ],
        self::INVALID_CREDENTIALS_ERROR => [
            'title' => '憑證無效',
            'detail' => '您提供的憑證無效，請重新登入。',
        ],
        self::DATABASE_ERROR => [
            'title' => '資料庫錯誤',
            'detail' => '操作資料庫時發生了問題，請稍後再試。',
        ],
    ];

    /**
     * 获取错误详情
     *
     * @param int $code 响应代码
     * @return array|null 错误详情
     */
    public static function getErrorDetails(int $code): ?array
    {
        return self::ERROR_DETAILS[$code] ?? null;
    }
}
