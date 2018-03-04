<?php
declare(strict_types = 1);

namespace Series\Command;

use Series\{
    Episode,
    Calendar,
    LastReport,
    Months,
    Exception\RuntimeException,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface,
    Period\Earth\Day
};
use Innmind\Immutable\{
    Str,
    Set,
};

final class Report implements Command
{
    private $calendar;
    private $clock;
    private $lastReport;

    public function __construct(
        Calendar $calendar,
        TimeContinuumInterface $clock,
        LastReport $lastReport
    ) {
        $this->calendar = $calendar;
        $this->clock = $clock;
        $this->lastReport = $lastReport;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        try {
            $since = $this->lastReport->when();
        } catch (RuntimeException $e) {
            $since = $this->clock->now()->goBack(new Day(1));
        }

        if ($arguments->contains('since')) {
            $since = $this->clock->at($arguments->get('since'));
        }

        $now = $this->clock->now();
        $months = (new Months)($since, $now);

        $months
            ->reduce(
                Set::of(Episode::class),
                function(Set $episodes, PointInTimeInterface $month): Set {
                    return $episodes->merge(
                        ($this->calendar)($month)
                    );
                }
            )
            ->filter(static function(Episode $episode) use ($since, $now): bool {
                return $episode->airedBetween($since, $now);
            })
            ->foreach(static function(Episode $episode) use ($env): void {
                $env->output()->write(
                    Str::of((string) $episode)->append("\n")
                );
            });
        $this->lastReport->at($now);
    }

    public function __toString(): string
    {
        return <<<USAGE
report [since]

List all the epsiodes you need to watch
USAGE;
    }
}
