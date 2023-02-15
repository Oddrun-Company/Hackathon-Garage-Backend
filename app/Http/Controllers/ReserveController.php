<?php

namespace App\Http\Controllers;

use App\Entities\WeekDay;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\ReservationRepository;
use App\Services\HolidayService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReserveController extends Controller
{
    private array $trans = [
        "Saturday" => "شنبه",
        "Sunday" => "یکشنبه",
        "Monday" => "دوشنبه",
        "Tuesday" => "سه شنبه",
        "Wednesday" => "چهارشنبه",
        "Thursday" => "پنجشنبه",
        "Friday" => "جمعه",
    ];

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
                $this->translateDayName($date->dayName),
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
            } else if (Carbon::parse($date)->isPast()) {
                $dayObj->setPrice(null);
                $dayObj->setStatus(ReservationStatus::PASSED);
            } else if (ReservationRepository::getRemainingParkingCapacity($date) == 0) {
                $dayObj->setPrice(null);
                $dayObj->setStatus(ReservationStatus::RESERVED_NOT_BIDABLE);
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
                $dayObj->setStatus(ReservationStatus::RESERVED_BUT_BIDABLE);
            }
            $nextWeek[$index] = $dayObj->toArray();
        }

        return $nextWeek;
    }

    private function getFirstDayOfCurrentWeek(): \Illuminate\Support\Carbon
    {
        return today()->startOfWeek()->subDays(2);
    }

    private function translateDayName($name): string
    {
        return $this->trans[$name];
    }

    public function reserve(Request $request, SmsService $sms): JsonResponse
    {
        $message = 'رزرو شد برو حالشو ببر.';
        $date = $request->get('date');
        $price = $request->get('price');
        $userId = $request->user()->id;
        if (ReservationRepository::getRemainingParkingCapacity($date) != 0) {
            $result = ReservationRepository::reserve($date, $price, $userId);
        } else {
            $dateTimestamp = strtotime($date);
            if ($dateTimestamp - time() < 7 * 24 * 3600) {
                return response()->base(false, 'No capacity');
            }
            $result = ReservationRepository::kickSomeoneOut($date, $price, $userId,$sms);
            if (!$result) {
                $message = 'رزرو نشد قیمت کشید بالا';
            }

        }
        return response()->base($result, null, $message);


    }


}
