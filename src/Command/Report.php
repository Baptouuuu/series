<?php
declare(strict_types = 1);

namespace Series\Command;

use Series\{
    Episode,
    Calendar,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Immutable\Str;

final class Report implements Command
{
    private $calendar;
    private $clock;

    public function __construct(
        Calendar $calendar,
        TimeContinuumInterface $clock
    ) {
        $this->calendar = $calendar;
        $this->clock = $clock;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        ($this->calendar)($this->clock->now())->foreach(static function(Episode $episode) use ($env): void {
            $env->output()->write(
                Str::of((string) $episode)->append("\n")
            );
        });
    }

    public function __toString(): string
    {
        return <<<USAGE
report

List all the epsiodes you need to wath
USAGE;
    }
}
