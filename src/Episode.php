<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Str;

final class Episode
{
    private string $show;
    private int $season;
    private int $episode;
    private PointInTime $airedAt;

    public function __construct(
        string $show,
        int $season,
        int $episode,
        PointInTime $airedAt
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
        PointInTime $since,
        PointInTime $to
    ) {
        return $this->airedAt->aheadOf($since) &&
            ($to->aheadOf($this->airedAt) || $to->equals($this->airedAt));
    }

    public function __toString(): string
    {
        return Str::of('%s s%\'.02de%\'.02d')->sprintf(
            $this->show,
            (string) $this->season,
            (string) $this->episode
        )->toString();
    }
}
