<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HolidayService
{
    private static string $baseUrl = "http://holidayapi.ir/";

    public static function isGregorianDateHoliday(string $date): bool
    {
        $url = self::$baseUrl . "gregorian/";
        return Cache::remember("holiday:{$date}", 3600 * 24, function () use ($url, $date) {
            $response = Http::get($url . $date);
            return $response->json('is_holiday');
        });
    }
}
