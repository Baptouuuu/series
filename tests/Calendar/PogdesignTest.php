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
use Innmind\Url\Url;
use Innmind\MediaType\MediaType;
use Innmind\Stream\Readable;
use Innmind\TimeContinuum\Earth\PointInTime\PointInTime;
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
            ->method('__invoke')
            ->with($this->callback(static function($request): bool {
                return $request->url()->toString() === 'https://www.pogdesign.co.uk/cat/4-2018' &&
                    $request->method()->toString() === 'GET' &&
                    $request->protocolVersion()->toString() === '2.0';
            }))
            ->willReturn(
                new HttpResource(
                    Url::of('example.com'),
                    MediaType::null(),
                    (Map::of('string', Attribute::class))->put(
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
