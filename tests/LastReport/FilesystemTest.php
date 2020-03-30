<?php
declare(strict_types = 1);

namespace Tests\Series\LastReport;

use Series\{
    LastReport\Filesystem,
    LastReport,
    Exception\RuntimeException,
};
use Innmind\Filesystem\{
    Adapter\InMemory,
    Adapter,
    Name,
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
    Earth\Clock as Earth,
    Earth\Format\ISO8601,
};
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            LastReport::class,
            new Filesystem(
                $this->createMock(Adapter::class),
                'report.txt',
                $this->createMock(Clock::class)
            )
        );
    }

    public function testAt()
    {
        $report = new Filesystem(
            $adapter = new InMemory,
            'report.txt',
            $this->createMock(Clock::class)
        );
        $time = $this->createMock(PointInTime::class);
        $time
            ->expects($this->once())
            ->method('format')
            ->with(new ISO8601)
            ->willReturn('foo');

        $this->assertFalse($adapter->contains(new Name('report.txt')));
        $this->assertSame($report, $report->at($time));
        $this->assertTrue($adapter->contains(new Name('report.txt')));
        $this->assertSame('foo', $adapter->get(new Name('report.txt'))->content()->toString());
    }

    public function testThrowWhenNeverReported()
    {
        $this->expectException(RuntimeException::class);

        (new Filesystem(
            new InMemory,
            'foo',
            $this->createMock(Clock::class)
        ))->when();
    }

    public function testWhen()
    {
        $report = new Filesystem(
            new InMemory,
            'foo',
            $clock = new Earth
        );
        $time = $clock->at('2018-03-04');
        $report->at($time);

        $this->assertInstanceOf(PointInTime::class, $report->when());
        $this->assertNotSame($time, $report->when());
        $this->assertTrue($report->when()->equals($time));
    }
}
