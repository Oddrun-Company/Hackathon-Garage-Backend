<?php

namespace App\Http\Controllers;

use App\Entities\WeekDay;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use App\Services\HolidayService;
use App\Repositories\ReservationRepository;
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
        // $user = $request->user()->only(['name', 'debt']);
        $user = User::where('id', '=', 1)
            ->first()
            ->only(['name', 'debt']);

        $firstDayOfCurrentWeek = $this->getFirstDayOfCurrentWeek();
        $firstDayOfNextWeek = $this->getFirstDayOfCurrentWeek()->addDays(7);


        return [
            'active_tab' => $this->getActiveTab(),
            'user' => $user,
            'current' => $this->createWeekList($firstDayOfCurrentWeek),
            'next' => []
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
                env('MINIMUM_RESERVE_PRICE'),
                $isHoliday ? ReservationStatus::HOLIDAY : ReservationStatus::AVAILABLE
            ));
            $date->addDay();
        }

        return $list;
    }

    private function createCurrentWeekList()
    {

    }

    private function createNextWeekList()
    {

    }

    private function getFirstDayOfCurrentWeek(): \Illuminate\Support\Carbon
    {
        return today()->startOfWeek()->subDays(2);
    }

    private function translateDayName($name): string
    {
        return $this->trans[$name];
    }

    public function reserve(Request $request): JsonResponse
    {
        $result = true;
        $message = 'رزرو شد برو حالشو ببر.';
        $date = $request->get('date');
        $price = $request->get('price');

        $result = ReservationRepository::kickSomeoneOut($date, $price, 4);
        if (!$result) {
            $message = 'رزرو نشد قیمت کشید بالا';
        }

//        if (ReservationRepository::getRemainingParkingCapacity($date) != 0) {
//            $userId = User::find(1)->id;
//            $result = ReservationRepository::reserve($date, $price, 4);
//        } else {
//            try {
//                $result = ReservationRepository::kickSomeoneOut($date, $price, 4);
//            } catch (ValidationException $e) {
//                $result = false;
//                $message = $e->getMessage();
//            }
//        }
        return response()->base($result, null, $message);



    }


}
