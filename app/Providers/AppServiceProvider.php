<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\ReportRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 註冊應用程序的服務。
     * 
     * 這個方法將各種服務綁定到服務容器中。當應用程序需要這些服務時，
     * Laravel 的服務容器會自動解析它們並注入到需要的地方。
     */
    public function register()
    {
        // 將 UserRepositoryInterface 綁定到具體的 UserRepository 類。
        // 這樣，每當應用程序需要使用 UserRepositoryInterface 時，
        // Laravel 會自動提供 UserRepository 的實例。
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // 將 ReportRepositoryInterface 綁定到具體的 ReportRepository 類。
    }
    /**
     * 啟動應用程序的服務。
     * 
     * 這個方法通常用來執行在所有服務提供者註冊完畢後需要運行的邏輯。
     * 可以在這裡進行事件監聽器的註冊、路由的綁定等操作。
     */
    public function boot()
    {
        // 這裡通常會放置啟動時需要執行的代碼，但在這個例子中留空。
    }
}
