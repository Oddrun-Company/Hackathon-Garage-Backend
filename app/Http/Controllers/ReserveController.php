<?php

namespace App\Http\Controllers;

use App\Entities\WeekDay;
use App\Enums\ReservationStatus;
use App\Exceptions\ReserveNotAccepted;
use App\Repositories\ReservationRepository;
use App\Services\HolidayService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReserveController extends Controller
{
    public function list(Request $request)
    {
        $user = $request->user()->only(['name', 'debt']);

        return [
            'active_tab' => $this->getActiveTab(),
            'user' => $user,

            'current' => $this->createCurrentWeekList(),
            'next' => $this->createNextWeekList()
        ];
    }

    private function getActiveTab()
    {
        $date = today();
        if ($date->isFriday() || $date->isThursday()) {
            return 'next';
        }

        return "current";
    }

    private function createWeekList(\Illuminate\Support\Carbon $date)
    {
        $list = [];
        for ($i = 0; $i < 5; $i++) {
            $isHoliday = HolidayService::isGregorianDateHoliday($date->format("Y/m/d"));
            $list[] = (new WeekDay(
                $date->toDateString(),
                $date->dayName,
                $isHoliday ? null : (int)env('MINIMUM_RESERVE_PRICE'),
                $isHoliday ? ReservationStatus::HOLIDAY : ReservationStatus::AVAILABLE
            ));
            $date->addDay();
        }

        return $list;
    }

    private function createCurrentWeekList()
    {
        $currentWeek = $this->createWeekList($this->getFirstDayOfCurrentWeek());
        /**
         * @var WeekDay $dayObj
         */
        foreach ($currentWeek as $index => $dayObj) {
            $date = $dayObj->getDate();
            if (ReservationRepository::isReservedByUser(\request()->user()->id, $date)) {
                $dayObj->setPrice(null);
                $dayObj->setStatus(ReservationStatus::RESERVED_BY_ME);
            } else if (Carbon::parse($date)->addDay()->isPast()) {
                $dayObj->setPrice(null);
                $dayObj->setStatus(ReservationStatus::PASSED);
            } else if (ReservationRepository::getRemainingParkingCapacity($date) == 0) {
                $dayObj->setPrice(null);
                $dayObj->setStatus(ReservationStatus::FULL_NOT_BIDABLE);
            }
            $currentWeek[$index] = $dayObj->toArray();
        }

        return $currentWeek;
    }

    private function createNextWeekList()
    {
        $nextWeek = $this->createWeekList($this->getFirstDayOfCurrentWeek()->addWeek());
        /**
         * @var WeekDay $dayObj
         */
        foreach ($nextWeek as $index => $dayObj) {
            $date = $dayObj->getDate();
            if (ReservationRepository::isReservedByUser(\request()->user()->id, $date)) {
                $dayObj->setPrice(null);
                $dayObj->setStatus(ReservationStatus::RESERVED_BY_ME);
            } else if (ReservationRepository::getRemainingParkingCapacity($date) == 0) {
                $dayObj->setPrice($dayObj->getPrice() + (int)env("MINIMUM_RESERVE_PRICE_STEP"));
                $dayObj->setStatus(ReservationStatus::FULL_BUT_BIDABLE);
            }
            $nextWeek[$index] = $dayObj->toArray();
        }

        return $nextWeek;
    }

    private function getFirstDayOfCurrentWeek(): \Illuminate\Support\Carbon
    {
        return today()->startOfWeek()->subDays(2);
    }

    public function reserve(Request $request, SmsService $sms): JsonResponse
    {
        $message = trans("messages.success.reserved");
        $date = $request->get('date');
        $price = $request->get('price');
        $userId = $request->user()->id;

        if ($price % env("MINIMUM_RESERVE_PRICE_STEP") != 0) {
            $message = trans("messages.errors.bad_bid_price");
            $result = false;
        }
        else if(ReservationRepository::isReservedByUser($userId, $date)) {
            $result = false;
            $message = trans("messages.errors.already_reserved");
        } else {
            if (ReservationRepository::getRemainingParkingCapacity($date) != 0) {
                $price = env("MINIMUM_RESERVE_PRICE");
                $result = ReservationRepository::reserve($date, $price, $userId);
            } else {
                $dateTimestamp = strtotime($date);
                $now = Carbon::now();
                $weekEndDate = $now->endOfWeek()->subDays(2)->timestamp;
                $start = env("BID_DEADLINE_TIME");
                $time  = Carbon::now()->format("H:i");
                if (today()->isFriday() && $time >= $start) {
                    $result = false;
                    $message = trans("messages.errors.deadline_passed");
                }
                elseif ($dateTimestamp < $weekEndDate) {
                    $result = false;
                    $message = trans("messages.errors.parking_is_full");
                }
                else {
                    try {
                        $result = ReservationRepository::kickSomeoneOut($date, $price, $userId, $sms);
                    }
                    catch (ReserveNotAccepted $e) {
                        $result = false;
                        $message = $e->getMessage();
                    }
                }
            }
        }

        return response()->base($result, null, $message);
    }


}
