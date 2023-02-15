<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HolidayService
{
    private static string $baseUrl = "http://holidayapi.ir/";

    public static function isGregorianDateHoliday(string $date): bool
    {
        $url = self::$baseUrl . "gregorian/";
        $response = Http::get($url . $date);

        return  $response->json('is_holiday');
    }
}
