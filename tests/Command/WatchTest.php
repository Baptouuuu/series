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
    TimeContinuumInterface,
    PointInTimeInterface,
};
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
                $this->createMock(TimeContinuumInterface::class)
            )
        );
    }

    public function testInvoke()
    {
        $command = new Watch(
            $watching = $this->createMock(Storage::class),
            $notWatching = $this->createMock(Storage::class),
            $calendar = $this->createMock(Calendar::class),
            $clock = $this->createMock(TimeContinuumInterface::class)
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
            ->expects($this->at(1))
            ->method('add')
            ->with('foo');
        $notWatching
            ->expects($this->at(2))
            ->method('add')
            ->with('baz');
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
                new Episode('tbbt', 1, 1),
                new Episode('bar', 1, 1),
                new Episode('watev', 1, 1),
                new Episode('ys', 1, 1),
                new Episode('baz', 1, 1)
            ));

        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->any())
            ->method('input')
            ->willReturn(new class implements Readable, Selectable {
                public function close(): Stream
                {
                    return $this;
                }
                public function closed(): bool
                {
                    return false;
                }
                public function position(): Position
                {
                }
                public function seek(Position $position, Mode $mode = null): Stream
                {
                    return $this;
                }
                public function rewind(): Stream
                {
                    return $this;
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
                    return tmpfile();
                }
                public function read(int $length = null): Str
                {
                    return Str::of("1\n");
                }
                public function readLine(): Str
                {
                    return Str::of('not used');
                }
                public function __toString(): string
                {
                    return 'not used';
                }
            });
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->at(0))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return (string) $line === "Series to watch:\n";
            }));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return (string) $line === "[0] foo\n";
            }));
        $output
            ->expects($this->at(2))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return (string) $line === "[1] bar\n";
            }));
        $output
            ->expects($this->at(3))
            ->method('write')
            ->with($this->callback(static function($line): bool {
                return (string) $line === "[2] baz\n";
            }));

        $this->assertNull($command(
            $env,
            new Arguments,
            new Options
        ));
    }
}
