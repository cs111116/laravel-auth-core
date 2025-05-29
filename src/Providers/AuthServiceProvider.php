<?php

namespace Cs111116\AuthCore\Providers;

use Illuminate\Support\ServiceProvider;
use Cs111116\AuthCore\Repositories\Contracts\UserRepositoryInterface;
use Cs111116\AuthCore\Repositories\UserRepository;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * 註冊服務
     */
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * 啟動服務
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
