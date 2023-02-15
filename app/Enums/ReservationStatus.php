<?php

namespace App\Enums;

class ReservationStatus
{
    public const HOLIDAY              = 0;

    public const PASSED               = 1;

    public const AVAILABLE            = 2;

    public const RESERVED_BUT_BIDABLE = 3;

    public const RESERVED_NOT_BIDABLE = 4;

    public const RESERVED_BY_ME       = 5;
}
