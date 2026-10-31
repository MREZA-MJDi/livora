<?php

namespace App\Helpers;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Small, dependency-free Jalali calendar helpers for admin presentation.
 *
 * Database timestamps remain Gregorian. Convert only at the presentation
 * and reporting-boundary layers.
 */
final class JalaliHelper
{
    private const MONTHS = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    private const WEEKDAYS = [
        'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه',
        'پنجشنبه', 'جمعه', 'شنبه',
    ];

    /** @return array{0:int,1:int,2:int} */
    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy -= 1600;
        $gm--;
        $gd--;

        $gDayNo = 365 * $gy
            + intdiv($gy + 3, 4)
            - intdiv($gy + 99, 100)
            + intdiv($gy + 399, 400);

        for ($i = 0; $i < $gm; $i++) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if ($gm > 1 && self::isGregorianLeapYear($gy + 1600)) {
            $gDayNo++;
        }

        $gDayNo += $gd;
        $jDayNo = $gDayNo - 79;
        $jNp = intdiv($jDayNo, 12053);
        $jDayNo %= 12053;

        $jy = 979 + (33 * $jNp) + (4 * intdiv($jDayNo, 1461));
        $jDayNo %= 1461;

        if ($jDayNo >= 366) {
            $jy += intdiv($jDayNo - 1, 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }

        for ($i = 0; $i < 11 && $jDayNo >= $jDaysInMonth[$i]; $i++) {
            $jDayNo -= $jDaysInMonth[$i];
        }

        return [$jy, $i + 1, $jDayNo + 1];
    }

    /** @return array{0:int,1:int,2:int} */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > ($jm <= 6 ? 31 : ($jm <= 11 ? 30 : 30))) {
            throw new \\InvalidArgumentException('Invalid Jalali date.');
        }

        $jy += 1595;
        $days = -355668
            + (365 * $jy)
            + (intdiv($jy, 33) * 8)
            + intdiv(($jy % 33) + 3, 4)
            + $jd;

        $days += $jm < 7 ? (($jm - 1) * 31) : ((($jm - 7) * 30) + 186);

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;
        $leap = self::isGregorianLeapYear($gy);
        $gDaysInMonth = [31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        for ($gm = 1; $gm <= 12 && $gd > $gDaysInMonth[$gm - 1]; $gm++) {
            $gd -= $gDaysInMonth[$gm - 1];
        }

        return [$gy, $gm, $gd];
    }

    public static function formatDate(DateTimeInterface $date, bool $withWeekday = false): string
    {
        [$year, $month, $day] = self::toJalali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j')
        );

        $formatted = sprintf('%04d/%02d/%02d', $year, $month, $day);

        if ($withWeekday) {
            $formatted = self::WEEKDAYS[(int) $date->format('w')]
                .'، '.sprintf('%02d %s %04d', $day, self::MONTHS[$month], $year);
        }

        return self::toPersianDigits($formatted);
    }

    public static function monthName(int $month): string
    {
        return self::MONTHS[$month] ?? throw new \\InvalidArgumentException('Invalid Jalali month.');
    }

    public static function formatMonthYear(int $year, int $month): string
    {
        return self::toPersianDigits(self::monthName($month).' '.$year);
    }

    public static function monthStart(int $year, int $month, DateTimeZone $timezone): DateTimeImmutable
    {
        [$gy, $gm, $gd] = self::toGregorian($year, $month, 1);

        return new DateTimeImmutable(
            sprintf('%04d-%02d-%02d 00:00:00', $gy, $gm, $gd),
            $timezone
        );
    }

    public static function toPersianDigits(string $value): string
    {
        return strtr($value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    private static function isGregorianLeapYear(int $year): bool
    {
        return $year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0);
    }
}
