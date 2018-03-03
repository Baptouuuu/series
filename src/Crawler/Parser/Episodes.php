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
use Innmind\Immutable\{
    MapInterface,
    Set,
    Str,
};

final class Episodes implements Parser
{
    private $reader;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
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
                    static function(Set $series, ElementInterface $episode): Set {
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

                        return $series->add(new Episode(
                            $show,
                            (int) (string) $parts->get('season'),
                            (int) (string) $parts->get('episode')
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
