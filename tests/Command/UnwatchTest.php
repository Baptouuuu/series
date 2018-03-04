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

class UnwatchTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Unwatch(
                $this->createMock(Storage::class),
                $this->createMock(Storage::class)
            )
        );
    }

    public function testInvoke()
    {
        $command = new Unwatch(
            $watching = $this->createMock(Storage::class),
            $notWatching = $this->createMock(Storage::class)
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
                return (string) $line === "Shows to stop watching:\n";
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

    public function testUsage()
    {
        $expected = <<<USAGE
unwatch

Choose the series you want to stop watching
USAGE;

        $this->assertSame($expected, (string) new Unwatch(
            $this->createMock(Storage::class),
            $this->createMock(Storage::class)
        ));
    }
}
