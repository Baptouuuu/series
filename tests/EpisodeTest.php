<?php
declare(strict_types = 1);

namespace Tests\Series;

use Series\Episode;
use Innmind\TimeContinuum\PointInTime;
use PHPUnit\Framework\TestCase;

class EpisodeTest extends TestCase
{
    public function testInterface()
    {
        $episode = new Episode(
            'tbbt',
            1,
            6,
            $airedAt = $this->createMock(PointInTime::class)
        );
        $since = $this->createMock(PointInTime::class);
        $to = $this->createMock(PointInTime::class);
        $airedAt
            ->expects($this->at(0))
            ->method('aheadOf')
            ->with($since)
            ->willReturn(true);
        $airedAt
            ->expects($this->at(1))
            ->method('aheadOf')
            ->with($since)
            ->willReturn(true);
        $airedAt
            ->expects($this->at(2))
            ->method('aheadOf')
            ->with($since)
            ->willReturn(false);
        $to
            ->expects($this->at(0))
            ->method('aheadOf')
            ->with($airedAt)
            ->willReturn(true);
        $to
            ->expects($this->at(1))
            ->method('aheadOf')
            ->with($airedAt)
            ->willReturn(false);
        $to
            ->expects($this->at(2))
            ->method('equals')
            ->with($airedAt)
            ->willReturn(true);

        $this->assertSame('tbbt', $episode->show());
        $this->assertSame('tbbt s01e06', (string) $episode);
        $this->assertTrue($episode->airedBetween($since, $to));
        $this->assertTrue($episode->airedBetween($since, $to));
        $this->assertFalse($episode->airedBetween($since, $to));
    }
}
