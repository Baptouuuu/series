<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Set;

interface Calendar
{
    /**
     * @return Set<Episode>
     */
    public function __invoke(PointInTime $month): Set;
}
