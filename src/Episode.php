<?php
declare(strict_types = 1);

namespace Series;

use Innmind\Immutable\Str;

final class Episode
{
    private $show;
    private $season;
    private $episode;

    public function __construct(
        string $show,
        int $season,
        int $episode
    ) {
        $this->show = $show;
        $this->season = $season;
        $this->episode = $episode;
    }

    public function show(): string
    {
        return $this->show;
    }

    public function __toString(): string
    {
        return (string) Str::of('%s s%\'.02de%\'.02d')->sprintf(
            $this->show,
            $this->season,
            $this->episode
        );
    }
}
