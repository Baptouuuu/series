<?php
declare(strict_types = 1);

namespace Tests\Series\Visitor;

use Series\Visitor\Episodes;
use Innmind\Xml\Element;
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\Set;
use function Innmind\Html\bootstrap as html;
use PHPUnit\Framework\TestCase;

class EpisodesTest extends TestCase
{
    public function testInvoke()
    {
        $dom = html()(new Stream(\fopen('fixtures/pogdesign.html', 'r')));

        $episodes = (new Episodes)($dom);

        $this->assertInstanceOf(Set::class, $episodes);
        $this->assertSame(Element::class, (string) $episodes->type());
        $this->assertCount(552, $episodes);
        $episodes->foreach(function(Element $episode): void {
            $this->assertTrue($episode->attributes()->contains('id'));
        });
    }
}
