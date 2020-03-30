<?php
declare(strict_types = 1);

namespace Tests\Series\Time;

use Series\Time\UrlFormat;
use Innmind\TimeContinuum\Format;
use PHPUnit\Framework\TestCase;

class UrlFormatTest extends TestCase
{
    public function testInterface()
    {
        $format = new UrlFormat;

        $this->assertInstanceOf(Format::class, $format);
        $this->assertSame('n-Y', $format->toString());
    }
}
