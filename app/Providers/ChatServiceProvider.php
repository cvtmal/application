<?php

namespace App\Providers;

use App\Services\ChatService;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChatService::class, function ($app) {
            return new ChatService;
        });
    }

    public function boot(): void
    {
        //
    }
}
