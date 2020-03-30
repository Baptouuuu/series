<?php
declare(strict_types = 1);

namespace Series\Time;

use Innmind\TimeContinuum\Format;

final class UrlFormat implements Format
{
    public function toString(): string
    {
        return 'n-Y';
    }
}
