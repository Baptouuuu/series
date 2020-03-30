<?php
declare(strict_types = 1);

namespace Series\Command;

use Series\{
    Command\Report,
    Episode,
    Calendar,
    LastReport,
    Exception\RuntimeException,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime as PointInTimeInterface,
    Format,
    Earth\PointInTime\PointInTime,
    Earth\Format\ISO8601,
};
use Innmind\Stream\Writable;
use Innmind\Immutable\{
    Set,
    Map,
};
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Report(
                $this->createMock(Calendar::class),
                $this->createMock(Clock::class),
                $this->createMock(LastReport::class)
            )
        );
    }

    public function testReportSinceLastDayByDefault()
    {
        $command = new Report(
            $calendar = $this->createMock(Calendar::class),
            new class implements Clock {
                public function now(): PointInTimeInterface
                {
                    return new PointInTime('2018-03-04');
                }

                public function at(string $time, Format $format = null): PointInTimeInterface
                {
                    return new PointInTime($time);
                }
            },
            $lastReport = $this->createMock(LastReport::class)
        );
        $lastReport
            ->expects($this->once())
            ->method('when')
            ->will($this->throwException(new RuntimeException));
        $lastReport
            ->expects($this->once())
            ->method('at')
            ->with($this->callback(static function($now): bool {
                return $now->format(new ISO8601) === '2018-03-04T00:00:00+00:00';
            }));
        $calendar
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(Set::of(
                Episode::class,
                new Episode(
                    'foo',
                    1,
                    2,
                    new PointInTime('2018-03-02')
                ),
                new Episode(
                    'bar',
                    1,
                    3,
                    new PointInTime('2018-03-04')
                )
            ));
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->at(0))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return $line->toString() === "bar s01e03\n";
            }));

        $this->assertNull($command(
            $env,
            new Arguments,
            new Options
        ));
    }

    public function testReportSinceLastReport()
    {
        $command = new Report(
            $calendar = $this->createMock(Calendar::class),
            new class implements Clock {
                public function now(): PointInTimeInterface
                {
                    return new PointInTime('2018-03-04');
                }

                public function at(string $time, Format $format = null): PointInTimeInterface
                {
                    return new PointInTime($time);
                }
            },
            $lastReport = $this->createMock(LastReport::class)
        );
        $lastReport
            ->expects($this->once())
            ->method('when')
            ->willReturn(new PointInTime('2018-01-30'));
        $lastReport
            ->expects($this->once())
            ->method('at')
            ->with($this->callback(static function($now): bool {
                return $now->format(new ISO8601) === '2018-03-04T00:00:00+00:00';
            }));
        $calendar
            ->expects($this->at(0))
            ->method('__invoke')
            ->willReturn(Set::of(Episode::class));
        $calendar
            ->expects($this->at(1))
            ->method('__invoke')
            ->willReturn(Set::of(
                Episode::class,
                new Episode(
                    'foo',
                    1,
                    1,
                    new PointInTime('2018-02-01')
                )
            ));
        $calendar
            ->expects($this->at(2))
            ->method('__invoke')
            ->willReturn(Set::of(
                Episode::class,
                new Episode(
                    'foo',
                    1,
                    2,
                    new PointInTime('2018-03-02')
                ),
                new Episode(
                    'bar',
                    1,
                    3,
                    new PointInTime('2018-03-04')
                )
            ));
        $calendar
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->willReturn(Set::of(Episode::class));
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->at(0))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return $line->toString() === "foo s01e01\n";
            }));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return $line->toString() === "foo s01e02\n";
            }));
        $output
            ->expects($this->at(2))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return $line->toString() === "bar s01e03\n";
            }));

        $this->assertNull($command(
            $env,
            new Arguments,
            new Options
        ));
    }

    public function testReportSinceProvidedDate()
    {
        $command = new Report(
            $calendar = $this->createMock(Calendar::class),
            new class implements Clock {
                public function now(): PointInTimeInterface
                {
                    return new PointInTime('2018-03-04');
                }

                public function at(string $time, Format $format = null): PointInTimeInterface
                {
                    return new PointInTime($time);
                }
            },
            $lastReport = $this->createMock(LastReport::class)
        );
        $lastReport
            ->expects($this->once())
            ->method('when')
            ->willReturn(new PointInTime('2018-01-30'));
        $lastReport
            ->expects($this->once())
            ->method('at')
            ->with($this->callback(static function($now): bool {
                return $now->format(new ISO8601) === '2018-03-04T00:00:00+00:00';
            }));
        $calendar
            ->expects($this->at(0))
            ->method('__invoke')
            ->willReturn(Set::of(
                Episode::class,
                new Episode(
                    'foo',
                    1,
                    1,
                    new PointInTime('2018-02-01')
                )
            ));
        $calendar
            ->expects($this->at(1))
            ->method('__invoke')
            ->willReturn(Set::of(
                Episode::class,
                new Episode(
                    'foo',
                    1,
                    2,
                    new PointInTime('2018-03-02')
                ),
                new Episode(
                    'bar',
                    1,
                    3,
                    new PointInTime('2018-03-04')
                )
            ));
        $calendar
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturn(Set::of(Episode::class));
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->at(0))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return $line->toString() === "foo s01e02\n";
            }));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return $line->toString() === "bar s01e03\n";
            }));

        $this->assertNull($command(
            $env,
            new Arguments(
                Map::of('string', 'string')('since', '2018-02-27')
            ),
            new Options
        ));
    }

    public function testUsage()
    {
        $expected = <<<USAGE
report [since]

List all the epsiodes you need to watch
USAGE;

        $this->assertSame($expected, (new Report(
            $this->createMock(Calendar::class),
            $this->createMock(Clock::class),
            $this->createMock(LastReport::class)
        ))->toString());
    }
}
