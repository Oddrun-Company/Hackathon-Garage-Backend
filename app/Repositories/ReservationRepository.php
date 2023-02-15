<?php

namespace App\Repositories;

use App\Exceptions\ReserveNotAccepted;
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
        $minimumPrice = Reservation::query()->where('reserve_date', '=', $date)
            ->min('price');
        $randomReserve = Reservation::where('reserve_date', '=', $date)
            ->wherePrice($minimumPrice)
            ->inRandomOrder()
            ->first();
        $minStep = env("MINIMUM_RESERVE_PRICE_STEP");
        if ($price <= $randomReserve->price || $price < $randomReserve->price + $minStep) {
            throw new ReserveNotAccepted(trans("messages.errors.minimum_bid_price_raised"));
        }
        $randomReserve->deleted_by = $addedUserId;
        $randomReserve->save();
        $randomReserve->delete();

        self::reserve($date, $price, $addedUserId);

        $prevUser = User::where('id', '=', $randomReserve->user_id)->first();
        $prevUser->debt += $randomReserve->price;
        $prevUser->save();

        $smsService->send($prevUser->phone_number, trans('messages.sms.reservedCancel', [
            "date" => Jalalian::forge(strtotime($date))->format('%A %d %B')
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
