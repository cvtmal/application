<?php

namespace App\Providers;

use App\Services\ChatService;
use App\Services\SecurityService;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChatService::class, function ($app) {
            return new ChatService($app->make(SecurityService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
