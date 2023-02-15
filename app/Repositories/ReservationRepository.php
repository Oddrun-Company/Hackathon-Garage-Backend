<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\User;
use Morilog\Jalali\Jalalian;

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
            ->exists();
    }

    public static function kickSomeoneOut($date, $price, $addedUserId, $smsService): bool
    {
        $firstActiveReserve = Reservation::query()->where('reserve_date', '=', $date)
            ->orderBy('price')
            ->first();
        $randomReserve = Reservation::where('reserve_date', '=', $date)
            ->wherePrice($firstActiveReserve->price)
            ->inRandomOrder()
            ->first();
        if ($price <= $randomReserve->price || $price < $randomReserve->price + env("MINIMUM_RESERVE_PRICE_STEP")) {
            return false;
        }
        $randomReserve->deleted_by = $addedUserId;
        $randomReserve->save();
        $randomReserve->delete();

        self::reserve($date, $price, $addedUserId);

        $prevUser = User::where('id', '=', $randomReserve->user_id)->first();
        $prevUser->debt += $randomReserve->price;
        $prevUser->save();

        $smsService->send($prevUser->phone_number, trans('messages.sms.reservedCancel', [
            "date" => Jalalian::fromFormat('Y-m-d', $date)->format('%A %d %B')
        ]));

        return true;
    }

    public static function reserve($date, $price, $userId): bool {
        $result = Reservation::create([
            'user_id' => $userId,
            'reserve_date' => $date,
            'price' => $price
        ]);
        $user = User::where('id', '=', $userId)->first();
        $user->debt -= $price;
        $user->save();

        return true;
    }


}
