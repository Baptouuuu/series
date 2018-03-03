<?php
declare(strict_types = 1);

namespace Series\Command;

use Series\{
    Command\Report,
    Episode,
    Calendar,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface,
};
use Innmind\Stream\Writable;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Report(
                $this->createMock(Calendar::class),
                $this->createMock(TimeContinuumInterface::class)
            )
        );
    }

    public function testInvoke()
    {
        $command = new Report(
            $calendar = $this->createMock(Calendar::class),
            $clock = $this->createMock(TimeContinuumInterface::class)
        );
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTimeInterface::class));
        $calendar
            ->expects($this->once())
            ->method('__invoke')
            ->with($now)
            ->willReturn(Set::of(
                Episode::class,
                new Episode('foo', 1, 1),
                new Episode('foo', 1, 2),
                new Episode('bar', 1, 3)
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
                return (string) $line === "foo s01e01\n";
            }));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return (string) $line === "foo s01e02\n";
            }));
        $output
            ->expects($this->at(2))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return (string) $line === "bar s01e03\n";
            }));

        $this->assertNull($command(
            $env,
            new Arguments,
            new Options
        ));
    }
}
