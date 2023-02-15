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

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getDayLabel(): string
    {
        return $this->dayLabel;
    }

    /**
     * @param string $dayLabel
     */
    public function setDayLabel(string $dayLabel): void
    {
        $this->dayLabel = $dayLabel;
    }

    /**
     * @return int|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * @param int|null $price
     */
    public function setPrice(?int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
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
