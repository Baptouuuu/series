<?php
declare(strict_types = 1);

namespace Series\Calendar;

use Series\{
    Calendar,
    Time\UrlFormat,
};
use Innmind\Crawler\Crawler;
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Http\{
    Message\Request\Request,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
};
use Innmind\Url\Url;
use Innmind\Immutable\SetInterface;

final class Pogdesign implements Calendar
{
    private $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(PointInTimeInterface $month): SetInterface
    {
        return $this
            ->crawler
            ->execute(
                new Request(
                    Url::fromString('https://www.pogdesign.co.uk/cat/'.$month->format(new UrlFormat)),
                    new Method('GET'),
                    new ProtocolVersion(2, 0)
                )
            )
            ->attributes()
            ->get('episodes')
            ->content();
    }
}