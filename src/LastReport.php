<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\PointInTimeInterface;

interface LastReport
{
    public function at(PointInTimeInterface $time): self;

    /**
     * @throws RuntimeException When no report already done
     */
    public function when(): PointInTimeInterface;
}
