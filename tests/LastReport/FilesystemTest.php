<?php
declare(strict_types = 1);

namespace Tests\Series\LastReport;

use Series\{
    LastReport\Filesystem,
    LastReport,
    Exception\RuntimeException,
};
use Innmind\Filesystem\{
    Adapter\MemoryAdapter,
    Adapter,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    TimeContinuum\Earth,
    PointInTimeInterface,
    Format\ISO8601,
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
                $this->createMock(TimeContinuumInterface::class)
            )
        );
    }

    public function testAt()
    {
        $report = new Filesystem(
            $adapter = new MemoryAdapter,
            'report.txt',
            $this->createMock(TimeContinuumInterface::class)
        );
        $time = $this->createMock(PointInTimeInterface::class);
        $time
            ->expects($this->once())
            ->method('format')
            ->with(new ISO8601)
            ->willReturn('foo');

        $this->assertFalse($adapter->has('report.txt'));
        $this->assertSame($report, $report->at($time));
        $this->assertTrue($adapter->has('report.txt'));
        $this->assertSame('foo', (string) $adapter->get('report.txt')->content());
    }

    public function testThrowWhenNeverReported()
    {
        $this->expectException(RuntimeException::class);

        (new Filesystem(
            new MemoryAdapter,
            'foo',
            $this->createMock(TimeContinuumInterface::class)
        ))->when();
    }

    public function testWhen()
    {
        $report = new Filesystem(
            new MemoryAdapter,
            'foo',
            $clock = new Earth
        );
        $time = $clock->at('2018-03-04');
        $report->at($time);

        $this->assertInstanceOf(PointInTimeInterface::class, $report->when());
        $this->assertNotSame($time, $report->when());
        $this->assertTrue($report->when()->equals($time));
    }
}
