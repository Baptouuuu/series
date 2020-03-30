<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\Str;

final class Episode
{
    private string $show;
    private int $season;
    private int $episode;
    private PointInTimeInterface $airedAt;

    public function __construct(
        string $show,
        int $season,
        int $episode,
        PointInTimeInterface $airedAt
    ) {
        $this->show = $show;
        $this->season = $season;
        $this->episode = $episode;
        $this->airedAt = $airedAt;
    }

    public function show(): string
    {
        return $this->show;
    }

    public function airedBetween(
        PointInTimeInterface $since,
        PointInTimeInterface $to
    ) {
        return $this->airedAt->aheadOf($since) &&
            ($to->aheadOf($this->airedAt) || $to->equals($this->airedAt));
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
