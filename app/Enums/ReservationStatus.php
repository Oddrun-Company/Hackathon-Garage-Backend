<?php

namespace App\Enums;

class ReservationStatus
{
    public const HOLIDAY              = 0;

    public const UNAVAILABLE          = 1;

    public const AVAILABLE            = 2;

    public const RESERVED_BUT_BIDABLE = 3;

    public const RESERVED_BY_ME       = 4;
}
