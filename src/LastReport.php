<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\PointInTime;

interface LastReport
{
    public function at(PointInTime $time): self;

    /**
     * @throws RuntimeException When no report already done
     */
    public function when(): PointInTime;
}
