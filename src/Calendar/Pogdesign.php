<?php
declare(strict_types = 1);

namespace Series\Calendar;

use Series\{
    Calendar,
    Time\UrlFormat,
};
use Innmind\Crawler\Crawler;
use Innmind\TimeContinuum\PointInTime;
use Innmind\Http\{
    Message\Request\Request,
    Message\Method,
    ProtocolVersion,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;

final class Pogdesign implements Calendar
{
    private Crawler $crawl;

    public function __construct(Crawler $crawl)
    {
        $this->crawl = $crawl;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(PointInTime $month): Set
    {
        return ($this->crawl)(
                new Request(
                    Url::of('https://www.pogdesign.co.uk/cat/'.$month->format(new UrlFormat)),
                    Method::get(),
                    new ProtocolVersion(2, 0)
                )
            )
            ->attributes()
            ->get('episodes')
            ->content();
    }
}
