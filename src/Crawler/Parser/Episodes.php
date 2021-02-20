<?php
declare(strict_types = 1);

namespace Series\Crawler\Parser;

use Series\{
    Episode,
    Visitor\Episodes as Visit,
};
use Innmind\Crawler\{
    Parser,
    HttpResource\Attribute\Attribute,
};
use Innmind\XML\{
    Reader,
    Element,
};
use Innmind\Http\Message\{
    Request,
    Response,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\{
    Map,
    Set,
    Str,
};

final class Episodes implements Parser
{
    private Reader $read;
    private Clock $clock;

    public function __construct(Reader $read, Clock $clock)
    {
        $this->read = $read;
        $this->clock = $clock;
    }

    public function __invoke(
        Request $request,
        Response $response,
        Map $attributes
    ): Map {
        $document = ($this->read)($response->body());
        $episodes = (new Visit)($document);

        return $attributes->put(
            self::key(),
            new Attribute(
                self::key(),
                $episodes->reduce(
                    Set::of(Episode::class),
                    function(Set $series, Element $episode): Set {
                        $show = \trim($episode
                            ->children()
                            ->get(1)
                            ->children()
                            ->get(4)
                            ->children()
                            ->get(0)
                            ->content());
                        $number = $episode
                            ->children()
                            ->get(1)
                            ->children()
                            ->get(4)
                            ->children()
                            ->get(2)
                            ->content();

                        $parts = Str::of($number)
                            ->trim()
                            ->capture('~s(?<season>\d{2})e(?<episode>\d{2})~');

                        $airedAt = Str::of($episode->attributes()->get('id')->value());
                        $airedAtParts = $airedAt->capture('~^d_(?<day>\d{1,2})_(?<month>\d{1,2})_(?<year>\d{4})$~');
                        $airedAt = Str::of('%s-%\'.02d-%\'.02d 00:00:00')->sprintf(
                            $airedAtParts->get('year')->toString(),
                            $airedAtParts->get('month')->toString(),
                            $airedAtParts->get('day')->toString(),
                        );

                        return $series->add(new Episode(
                            $show,
                            (int) $parts->get('season')->toString(),
                            (int) $parts->get('episode')->toString(),
                            $this->clock->at($airedAt->toString())
                        ));
                    }
                )
            )
        );
    }

    public static function key(): string
    {
        return 'episodes';
    }
}
