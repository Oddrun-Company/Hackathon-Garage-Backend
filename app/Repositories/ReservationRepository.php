<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use function Psy\debug;

class ReservationRepository
{

    public static function getRemainingParkingCapacity(string $date): int
    {
        $count = Reservation::where('reserve_date', '=', $date)->count();
        return env('LIMIT_PARKING') - $count;
    }

    /**
     * @throws ValidationException
     */
    public static function kickSomeoneOut($date, $price, $addedUserId): bool
    {
        $reserved = Reservation::query()->where('reserve_date', '=', $date)
            ->where('deleted_by', '=', null)
            ->orderBy('price')
            ->first();
        if ($price <= $reserved->price) {
            return false;
        }
        Reservation::where('user_id', $reserved->user_id)->update(['deleted_by' => $addedUserId]);
        self::reserve($date, $price, $addedUserId);

        $rDebt = User::where('id', '=', $reserved->user_id)->first()->debt;
        $newDebt = $rDebt + $reserved->price;
        User::where('id', $reserved->user_id)->update(['debt' => $newDebt]);
        $nDebt = User::where('id', '=', $addedUserId)->first()->debt;
        $newDept = $nDebt - $price;
        User::where('id', $addedUserId)->update(['debt' => $newDept]);
        return true;

    }

    public static function reserve($date, $price, $userId): bool {
        $result = Reservation::insert([
            'user_id' => $userId,
            'reserve_date' => $date,
            'price' => $price
        ]);

        return $result;
    }


}
