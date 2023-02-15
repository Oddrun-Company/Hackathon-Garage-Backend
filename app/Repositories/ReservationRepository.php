<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\User;

class ReservationRepository
{

    public static function getRemainingParkingCapacity(string $date): int
    {
        $count = Reservation::where('reserve_date', '=', $date)->count();
        return env('LIMIT_PARKING') - $count;
    }

    public static function isReservedByUser(int $userId, string $date)
    {
        return Reservation::where('user_id', '=', $userId)
            ->where('reserve_date', '=', $date)
            ->exist();
    }

    public static function kickSomeoneOut($date, $price, $addedUserId): void
    {
        $reserved = Reservation::query()->where('reserve_date', '=', $date)
            ->where('deleted_by', '=', null)
            ->orderBy('price')
            ->first();
        if ($price <= $reserved->price) {
            //return error
            dd("can not reserve");
        }
        Reservation::where('user_id', $reserved->user_id)->update(['deleted_by' => $addedUserId]);
        self::reserve($date, $price, $addedUserId);

        $rDept = User::where('id', '=', $reserved->user_id)->dept;
        $newDept = $rDept + $reserved->price;


        $nDept = User::where('id', '=', $addedUserId)->dept;
        $newDept = $nDept - $price;

    }

    public static function reserve($date, $price, $userId): bool
    {
        return Reservation::insert([
            'user_id' => $userId,
            'reserve_date' => $date,
            'price' => $price
        ]);
    }


}
