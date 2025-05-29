<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * UserRepository 是 UserRepositoryInterface 的具體實現，
 * 負責處理與 User 模型相關的數據庫操作。
 * 
 * 這個類將用戶數據的查找和創建操作與具體的業務邏輯分離，增強了
 * 代碼的可維護性和可測試性。所有數據操作通過 Eloquent 模型 User 進行。
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * 根據用戶的電子郵件查找用戶數據。
     * 
     * @param string $email 用戶的電子郵件地址
     * @return User|null 查找到的用戶數據，如果沒有找到則返回 null
     */
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * 創建一個新用戶。
     * 
     * @param array $data 包含用戶數據的關聯陣列
     * @return User 創建後的用戶數據，通常是 User 模型實例
     */
    public function create(array $data)
    {
        return User::create($data);
    }
}
