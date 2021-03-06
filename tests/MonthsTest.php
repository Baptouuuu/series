<?php
declare(strict_types = 1);

namespace Tests\Series;

use Series\Months;
use Innmind\TimeContinuum\{
    PointInTime as PointInTimeInterface,
    Earth\PointInTime\PointInTime,
    Earth\Format\ISO8601,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class MonthsTest extends TestCase
{
    public function testFromSameMonth()
    {
        $months = (new Months)(
            new PointInTime('2018-02-12 12:13:14.15'),
            new PointInTime('2018-02-28 13:14:15.16')
        );

        $this->assertInstanceOf(Set::class, $months);
        $this->assertSame(PointInTimeInterface::class, (string) $months->type());
        $this->assertCount(1, $months);
        $months = unwrap($months);
        $this->assertSame(
            '2018-02-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
    }

    public function testAccrossMultipleMonths()
    {
        $months = (new Months)(
            new PointInTime('2018-02-12 12:13:14.15'),
            new PointInTime('2018-06-02 13:14:15.16')
        );

        $this->assertCount(5, $months);
        $months = unwrap($months);
        $this->assertSame(
            '2018-02-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
        \next($months);
        $this->assertSame(
            '2018-03-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
        \next($months);
        $this->assertSame(
            '2018-04-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
        \next($months);
        $this->assertSame(
            '2018-05-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
        \next($months);
        $this->assertSame(
            '2018-06-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
    }

    public function testJumpOneMonth()
    {
        $months = (new Months)(
            new PointInTime('2018-02-28 12:13:14.15'),
            new PointInTime('2018-03-01 13:14:15.16')
        );

        $this->assertCount(2, $months);
        $months = unwrap($months);
        $this->assertSame(
            '2018-02-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
        \next($months);
        $this->assertSame(
            '2018-03-01T00:00:00+00:00',
            \current($months)->format(new ISO8601)
        );
    }
}
