<?php
declare(strict_types = 1);

namespace Tests\Series;

use Series\Episode;
use PHPUnit\Framework\TestCase;

class EpisodeTest extends TestCase
{
    public function testInterface()
    {
        $episode = new Episode('tbbt', 1, 6);

        $this->assertSame('tbbt', $episode->show());
        $this->assertSame('tbbt s01e06', (string) $episode);
    }
}
