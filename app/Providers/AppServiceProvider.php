<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('base', function ($success = true, $data = null, $message = null) {
            return Response::json([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ]);
        });
    }
}
