<?php
declare(strict_types = 1);

namespace Tests\Series\Visitor;

use Series\Visitor\Episodes;
use Innmind\Html\{
    Reader\Reader,
    Translator\NodeTranslators as HtmlTranslators,
};
use Innmind\Xml\{
    ElementInterface,
    Translator\NodeTranslator,
    Translator\NodeTranslators,
};
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\SetInterface;
use PHPUnit\Framework\TestCase;

class EpisodesTest extends TestCase
{
    public function testInvoke()
    {
        $reader = new Reader(
            new NodeTranslator(
                NodeTranslators::defaults()->merge(
                    HtmlTranslators::defaults()
                )
            )
        );
        $dom = $reader->read(new Stream(fopen('fixtures/pogdesign.html', 'r')));

        $episodes = (new Episodes)($dom);

        $this->assertInstanceOf(SetInterface::class, $episodes);
        $this->assertSame(ElementInterface::class, (string) $episodes->type());
        $this->assertCount(552, $episodes);
        $episodes->foreach(function(ElementInterface $episode): void {
            $this->assertTrue($episode->attributes()->contains('id'));
        });
    }
}
