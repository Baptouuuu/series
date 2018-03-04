<?php
declare(strict_types = 1);

namespace Series\Time;

use Innmind\TimeContinuum\FormatInterface;

final class UrlFormat implements FormatInterface
{
    public function __toString(): string
    {
        return 'n-Y';
    }
}
