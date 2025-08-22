<?php

namespace App\Helper;

use DateInterval;
use RoundingMode;

final readonly class DateIntervalHelper
{
    private const int SECOND = 1;
    private const int MINUTE = 60 * self::SECOND;
    private const int HOUR = 60 * self::MINUTE;
    private const int DAY = 24 * self::HOUR;
    private const int MONTH = 30 * self::DAY;
    private const int YEAR = 365 * self::DAY;

    public static function compare(DateInterval $left, DateInterval $right): int
    {
        return self::convertToSeconds($left) <=> self::convertToSeconds($right);
    }

    public static function toDays(DateInterval $interval, RoundingMode $roundingMode = RoundingMode:: PositiveInfinity): int
    {
        return round(self::convertToSeconds($interval) / self::DAY, $roundingMode);
    }

    public static function toString(DateInterval $interval): string
    {
        $result = 'P';

        if ($interval->y) {
            $result .= "{$interval->y}Y";
        }
        if ($interval->m) {
            $result .= "{$interval->m}M";
        }
        if ($interval->d) {
            $result .= "{$interval->d}D";
        }
        $result .= 'T';
        if ($interval->h) {
            $result .= "{$interval->h}H";
        }
        if ($interval->i) {
            $result .= "{$interval->i}M";
        }
        if ($interval->s) {
            $result .= "{$interval->s}S";
        }

        return $result;
    }

    public static function convertToSeconds(DateInterval $interval): int
    {
        $total = 0;

        if ($interval->y) {
            $total += abs($interval->y) * self::YEAR;
        }
        if ($interval->m) {
            $total += abs($interval->m) * self::MONTH;
        }
        if ($interval->d) {
            $total += abs($interval->d) * self::DAY;
        }
        if ($interval->h) {
            $total += abs($interval->h) * self::HOUR;
        }
        if ($interval->i) {
            $total += abs($interval->i) * self::MINUTE;
        }
        if ($interval->s) {
            $total += abs($interval->s) * self::SECOND;
        }

        return $total;
    }
}
