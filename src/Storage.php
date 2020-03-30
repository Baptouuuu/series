<?php
declare(strict_types = 1);

namespace Series;

use Innmind\Immutable\Set;

interface Storage
{
    public function add(string $series): self;
    public function remove(string $series): self;

    /**
     * @return Set<string>
     */
    public function all(): Set;
}
