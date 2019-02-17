<?php
declare(strict_types = 1);

namespace Tests\Series;

use function Series\bootstrap;
use Innmind\Filesystem\Adapter;
use Innmind\HttpTransport\Transport;
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\CLI\Commands;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testInvokation()
    {
        $commands = bootstrap(
            $this->createMock(Adapter::class),
            $this->createMock(Transport::class),
            $this->createMock(TimeContinuumInterface::class)
        );

        $this->assertInstanceOf(Commands::class, $commands);
    }
}
