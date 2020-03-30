<?php
declare(strict_types = 1);

namespace Tests\Series\Crawler\Parser;

use Series\{
    Crawler\Parser\Episodes,
    Episode,
};
use Innmind\Crawler\{
    Parser,
    HttpResource\Attribute,
};
use Innmind\Http\Message\{
    Request,
    Response,
};
use Innmind\Stream\Readable\Stream;
use Innmind\TimeContinuum\Earth\Clock as Earth;
use Innmind\Immutable\{
    Map,
    Set,
};
use function Innmind\Immutable\first;
use function Innmind\Html\bootstrap as html;
use PHPUnit\Framework\TestCase;

class EpisodesTest extends TestCase
{
    public function testInterface()
    {
        $parse = new Episodes(
            html(),
            $clock = new Earth
        );

        $this->assertInstanceOf(Parser::class, $parse);
        $this->assertSame('episodes', Episodes::key());

        $response = $this->createMock(Response::class);
        $response
            ->expects($this->once())
            ->method('body')
            ->willReturn(new Stream(fopen('fixtures/pogdesign.html', 'r')));

        $attributes = $parse(
            $this->createMock(Request::class),
            $response,
            Map::of('string', Attribute::class)
        );

        $this->assertInstanceOf(Map::class, $attributes);
        $this->assertSame('string', (string) $attributes->keyType());
        $this->assertSame(Attribute::class, (string) $attributes->valueType());
        $this->assertCount(1, $attributes);
        $episodes = $attributes->get('episodes')->content();
        $this->assertInstanceOf(Set::class, $episodes);
        $this->assertSame(Episode::class, (string) $episodes->type());
        $this->assertCount(552, $episodes);
        $this->assertTrue(
            first($episodes)->airedBetween(
                $clock->at('2018-02-28 23:59:59'),
                $clock->at('2018-03-01 00:00:01')
            )
        );
    }
}
