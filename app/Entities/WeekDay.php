<?php

namespace App\Entities;

use App\Enums\ReservationStatus;

class WeekDay
{
    private string $date;

    private string $dayLabel;

    private ?int   $price;

    private int   $status;

    public function __construct(string $date, string $dayLabel, ?int $price, int $status)
    {
        $this->date = $date;
        $this->dayLabel = $dayLabel;
        $this->price = $price;
        $this->status = $status;
    }

    public function toArray()
    {
        return [
            "date" => $this->date,
            "day_label" => $this->dayLabel,
            "price" => $this->price,
            "status" => $this->status
        ];
    }
}
