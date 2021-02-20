<?php
declare(strict_types = 1);

namespace Tests\Series\Command;

use Series\{
    Command\Watch,
    Storage,
    Calendar,
    Episode,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\OperatingSystem\Sockets;
use Innmind\Stream\{
    Stream,
    Stream\Position,
    Stream\Position\Mode,
    Stream\Size,
    Readable,
    Writable,
    Selectable,
};
use Innmind\Immutable\{
    Set,
    Str,
    Sequence,
};
use PHPUnit\Framework\TestCase;

class WatchTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Watch(
                $this->createMock(Storage::class),
                $this->createMock(Storage::class),
                $this->createMock(Calendar::class),
                $this->createMock(Clock::class),
                $this->createMock(Sockets::class),
            )
        );
    }

    public function testInvoke()
    {
        $command = new Watch(
            $watching = $this->createMock(Storage::class),
            $notWatching = $this->createMock(Storage::class),
            $calendar = $this->createMock(Calendar::class),
            $clock = $this->createMock(Clock::class),
            new Sockets\Unix,
        );
        $watching
            ->expects($this->once())
            ->method('all')
            ->willReturn(Set::of('string', 'tbbt', 'ys'));
        $watching
            ->expects($this->once())
            ->method('add')
            ->with('bar');
        $notWatching
            ->expects($this->once())
            ->method('all')
            ->willReturn(Set::of('string', 'watev'));
        $notWatching
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(['foo'], ['baz']);
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now = $this->createMock(PointInTime::class));
        $calendar
            ->expects($this->once())
            ->method('__invoke')
            ->with($now)
            ->willReturn(Set::of(
                Episode::class,
                new Episode('foo', 1, 1, $this->createMock(PointInTime::class)),
                new Episode('tbbt', 1, 1, $this->createMock(PointInTime::class)),
                new Episode('bar', 1, 1, $this->createMock(PointInTime::class)),
                new Episode('watev', 1, 1, $this->createMock(PointInTime::class)),
                new Episode('ys', 1, 1, $this->createMock(PointInTime::class)),
                new Episode('baz', 1, 1, $this->createMock(PointInTime::class))
            ));

        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->any())
            ->method('interactive')
            ->willReturn(true);
        $env
            ->expects($this->any())
            ->method('arguments')
            ->willReturn(Sequence::strings());
        $env
            ->expects($this->any())
            ->method('input')
            ->willReturn(new class implements Readable, Selectable {
                private $resource;

                public function close(): void
                {
                }
                public function closed(): bool
                {
                    return false;
                }
                public function position(): Position
                {
                }
                public function seek(Position $position, Mode $mode = null): void
                {
                }
                public function rewind(): void
                {
                }
                public function end(): bool
                {
                    return false;
                }
                public function size(): Size
                {
                }
                public function knowsSize(): bool
                {
                    return false;
                }
                public function resource()
                {
                    return $this->resource ?? $this->resource = \tmpfile();
                }
                public function read(int $length = null): Str
                {
                    return Str::of("1\n");
                }
                public function readLine(): Str
                {
                    return Str::of('not used');
                }
                public function toString(): string
                {
                    return 'not used';
                }
            });
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->exactly(5))
            ->method('write')
            ->withConsecutive(
                [$this->callback(static function($line): bool {
                    return $line->toString() === "Series to watch:\n";
                })],
                [$this->callback(static function($line): bool {
                    return $line->toString() === "[0] foo\n";
                })],
                [$this->callback(static function($line): bool {
                    return $line->toString() === "[1] bar\n";
                })],
                [$this->callback(static function($line): bool {
                    return $line->toString() === "[2] baz\n";
                })],
                [$this->callback(static function($line): bool {
                    return $line->toString() === '> ';
                })],
            );

        $this->assertNull($command(
            $env,
            new Arguments,
            new Options
        ));
    }

    public function testUsage()
    {
        $expected = <<<USAGE
watch

Choose the series you want to watch

Will display a list of series airing this month, you'll need
to pick the ones you want to follow.

You'll need to run this command every month if you want to
follow new shows
USAGE;

        $this->assertSame($expected, (new Watch(
            $this->createMock(Storage::class),
            $this->createMock(Storage::class),
            $this->createMock(Calendar::class),
            $this->createMock(Clock::class),
            $this->createMock(Sockets::class),
        ))->toString());
    }
}
