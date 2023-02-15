<?php

namespace App\Providers;

use Carbon\Carbon;
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

        Carbon::macro('isDayOff', function ($date) {
            return $date->isFriday();
        });
    }
}
