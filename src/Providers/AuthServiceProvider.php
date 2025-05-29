<?php

namespace GaryCodingTeam\AuthModule\Providers;

use Illuminate\Support\ServiceProvider;
use GaryCodingTeam\AuthModule\Repositories\Contracts\UserRepositoryInterface;
use GaryCodingTeam\AuthModule\Repositories\UserRepository;

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
