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
    ReaderInterface,
    ElementInterface,
};
use Innmind\Http\Message\{
    Request,
    Response,
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Immutable\{
    MapInterface,
    Set,
    Str,
};

final class Episodes implements Parser
{
    private $reader;
    private $clock;

    public function __construct(
        ReaderInterface $reader,
        TimeContinuumInterface $clock
    ) {
        $this->reader = $reader;
        $this->clock = $clock;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(
        Request $request,
        Response $response,
        MapInterface $attributes
    ): MapInterface {
        $document = $this->reader->read($response->body());
        $episodes = (new Visit)($document);

        return $attributes->put(
            self::key(),
            new Attribute(
                self::key(),
                $episodes->reduce(
                    new Set(Episode::class),
                    function(Set $series, ElementInterface $episode): Set {
                        $show = trim($episode
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
                            (string) $airedAtParts->get('year'),
                            (string) $airedAtParts->get('month'),
                            (string) $airedAtParts->get('day')
                        );

                        return $series->add(new Episode(
                            $show,
                            (int) (string) $parts->get('season'),
                            (int) (string) $parts->get('episode'),
                            $this->clock->at((string) $airedAt)
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
