<?php
declare(strict_types = 1);

namespace Tests\Series\Time;

use Series\Time\UrlFormat;
use Innmind\TimeContinuum\FormatInterface;
use PHPUnit\Framework\TestCase;

class UrlFormatTest extends TestCase
{
    public function testInterface()
    {
        $format = new UrlFormat;

        $this->assertInstanceOf(FormatInterface::class, $format);
        $this->assertSame('n-Y', (string) $format);
    }
}
