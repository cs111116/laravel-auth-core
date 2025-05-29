<?php

namespace App\Repositories\Contracts;

/**
 * UserRepositoryInterface 定義了用戶數據存取的標準行為。
 * 
 * 這個接口用來確保所有實現該接口的類都提供統一的數據存取方法，
 * 例如根據電子郵件查找用戶和創建新用戶的方法。這樣做有助於將
 * 業務邏輯與具體的數據存取邏輯分離，增強代碼的可維護性和可測試性。
 */
interface UserRepositoryInterface
{
    /**
     * 根據用戶的電子郵件查找用戶數據。
     * 
     * @param string $email 用戶的電子郵件地址
     * @return mixed 查找到的用戶數據，通常是 User 模型實例
     */
    public function findByEmail(string $email);

    /**
     * 創建一個新用戶。
     * 
     * @param array $data 包含用戶數據的關聯陣列
     * @return mixed 創建後的用戶數據，通常是 User 模型實例
     */
    public function create(array $data);
}
