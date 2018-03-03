<?php
declare(strict_types = 1);

namespace Series;

use Innmind\Immutable\SetInterface;

interface Storage
{
    public function add(string $series): self;
    public function remove(string $series): self;

    /**
     * @return SetInterface<string>
     */
    public function all(): SetInterface;
}
