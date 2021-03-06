<?php
declare(strict_types = 1);

namespace Tests\Series\Calendar;

use Series\{
    Calendar\StorageAware,
    Calendar,
    Storage,
    Episode,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class StorageAwareTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Calendar::class,
            new StorageAware(
                $this->createMock(Calendar::class),
                $this->createMock(Storage::class)
            )
        );
    }

    public function testInvoke()
    {
        $calendar = new StorageAware(
            $mock = $this->createMock(Calendar::class),
            $storage = $this->createMock(Storage::class)
        );
        $time = $this->createMock(PointInTime::class);
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($time)
            ->willReturn(Set::of(
                Episode::class,
                $foo = new Episode('foo', 1, 2, $this->createMock(PointInTime::class)),
                new Episode('bar', 3, 4, $this->createMock(PointInTime::class)),
                $baz = new Episode('baz', 5, 6, $this->createMock(PointInTime::class)),
                new Episode('watev', 7, 8, $this->createMock(PointInTime::class))
            ));
        $storage
            ->expects($this->once())
            ->method('all')
            ->willReturn(Set::of('string', 'baz', 'foo'));

        $episodes = $calendar($time);

        $this->assertInstanceOf(Set::class, $episodes);
        $this->assertSame(Episode::class, (string) $episodes->type());
        $this->assertSame([$foo, $baz], unwrap($episodes));
    }
}
