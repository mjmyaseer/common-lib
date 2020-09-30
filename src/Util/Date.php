<?php
declare(strict_types=1);

namespace Linx\Lib\Util;

use DateTime;
use DateTimeZone;

class Date
{
    public static function fromString(DateTime $dateTime): DateTime
    {
        return new DateTime($dateTime, new DateTimeZone(env('APP_TIMEZONE')));
    }

    public static function toString(DateTime $dateTime, string $format = 'Y-m-d'): string
    {
        return $dateTime->format($format);
    }

}