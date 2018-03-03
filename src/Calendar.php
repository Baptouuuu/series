<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\SetInterface;

interface Calendar
{
    /**
     * @return SetInterface<Episode>
     */
    public function __invoke(PointInTimeInterface $month): SetInterface;
}
