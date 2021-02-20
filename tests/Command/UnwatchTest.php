<?php
declare(strict_types = 1);

namespace Tests\Series\Command;

use Series\{
    Command\Unwatch,
    Storage,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
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

class UnwatchTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Unwatch(
                $this->createMock(Storage::class),
                $this->createMock(Storage::class),
                $this->createMock(Sockets::class),
            )
        );
    }

    public function testInvoke()
    {
        $command = new Unwatch(
            $watching = $this->createMock(Storage::class),
            $notWatching = $this->createMock(Storage::class),
            new Sockets\Unix,
        );
        $watching
            ->expects($this->once())
            ->method('all')
            ->willReturn(Set::of('string', 'foo', 'bar', 'baz'));
        $watching
            ->expects($this->once())
            ->method('remove')
            ->with('bar');
        $notWatching
            ->expects($this->once())
            ->method('add')
            ->with('bar');
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
                    return $line->toString() === "Shows to stop watching:\n";
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
unwatch

Choose the series you want to stop watching
USAGE;

        $this->assertSame($expected, (new Unwatch(
            $this->createMock(Storage::class),
            $this->createMock(Storage::class),
            $this->createMock(Sockets::class),
        ))->toString());
    }
}
