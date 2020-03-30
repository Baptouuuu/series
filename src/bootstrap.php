<?php
declare(strict_types = 1);

namespace Series;

use Innmind\Filesystem\Adapter;
use Innmind\HttpTransport\Transport;
use Innmind\TimeContinuum\Clock;
use Innmind\OperatingSystem\Sockets;
use Innmind\CLI\Commands;
use Innmind\Crawler\Crawler\Crawler as Crawl;
use function Innmind\Html\bootstrap as html;

function bootstrap(
    Adapter $filesystem,
    Transport $transport,
    Clock $clock,
    Sockets $sockets
): Commands {
    $watching = new Storage\Filesystem(
        $filesystem,
        'watching.txt'
    );
    $notWatching = new Storage\Filesystem(
        $filesystem,
        'notWatching.txt'
    );
    $calendar = new Calendar\Pogdesign(
        new Crawl(
            $transport,
            new Crawler\Parser\Episodes(
                html(),
                $clock
            )
        )
    );

    return new Commands(
        new Command\Watch($watching, $notWatching, $calendar, $clock, $sockets),
        new Command\Unwatch($watching, $notWatching, $sockets),
        new Command\Report(
            new Calendar\StorageAware(
                $calendar,
                $watching
            ),
            $clock,
            new LastReport\Filesystem(
                $filesystem,
                'lastReport.txt',
                $clock
            )
        )
    );
}
