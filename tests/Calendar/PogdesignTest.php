<?php
declare(strict_types = 1);

namespace Tests\Series\Calendar;

use Series\{
    Calendar\Pogdesign,
    Calendar,
    Episode,
};
use Innmind\Crawler\{
    Crawler,
    HttpResource,
    HttpResource\Attribute,
};
use Innmind\Url\UrlInterface;
use Innmind\Filesystem\MediaType;
use Innmind\Stream\Readable;
use Innmind\TimeContinuum\PointInTime\Earth\PointInTime;
use Innmind\Immutable\{
    Map,
    Set
};
use PHPUnit\Framework\TestCase;

class PogdesignTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Calendar::class,
            new Pogdesign($this->createMock(Crawler::class))
        );
    }

    public function testInvoke()
    {
        $calendar = new Pogdesign(
            $crawler = $this->createMock(Crawler::class)
        );
        $crawler
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($request): bool {
                return (string) $request->url() === 'https://www.pogdesign.co.uk/cat/4-2018' &&
                    (string) $request->method() === 'GET' &&
                    (string) $request->protocolVersion() === '2.0';
            }))
            ->willReturn(
                new HttpResource(
                    $this->createMock(UrlInterface::class),
                    $this->createMock(MediaType::class),
                    (new Map('string', Attribute::class))->put(
                        'episodes',
                        new Attribute\Attribute(
                            'epsiodes',
                            $expected = Set::of(Episode::class)
                        )
                    ),
                    $this->createMock(Readable::class)
                )
            );
        $time = new PointInTime('2018-04-03');

        $this->assertSame($expected, $calendar($time));
    }
}
