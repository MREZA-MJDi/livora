<?php

namespace Tests\\Unit;

use App\\Helpers\\JalaliHelper;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\\Framework\\TestCase;

class JalaliHelperTest extends TestCase
{
    public function test_gregorian_dates_convert_to_jalali_dates(): void
    {
        $this->assertSame([1404, 1, 1], JalaliHelper::toJalali(2025, 3, 21));
        $this->assertSame([1405, 8, 10], JalaliHelper::toJalali(2026, 11, 1));
    }

    public function test_jalali_dates_convert_back_to_gregorian_dates(): void
    {
        $this->assertSame([2025, 3, 21], JalaliHelper::toGregorian(1404, 1, 1));
        $this->assertSame([2026, 11, 1], JalaliHelper::toGregorian(1405, 8, 10));
    }

    public function test_dashboard_date_format_uses_jalali_calendar_and_persian_digits(): void
    {
        $date = new DateTimeImmutable('2025-03-21 12:00:00', new DateTimeZone('Asia/Tehran'));

        $this->assertSame('۱۴۰۴/۰۱/۰۱', JalaliHelper::formatDate($date));
    }

    public function test_jalali_month_start_maps_to_gregorian_month_boundary(): void
    {
        $start = JalaliHelper::monthStart(1404, 1, new DateTimeZone('Asia/Tehran'));

        $this->assertSame('2025-03-21 00:00:00', $start->format('Y-m-d H:i:s'));
    }
}
