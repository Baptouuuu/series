<?php
declare(strict_types = 1);

namespace Series\Command;

use Series\{
    Storage,
    Calendar,
    Episode,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
    Question\ChoiceQuestion,
};
use Innmind\TimeContinuum\Clock;
use Innmind\OperatingSystem\Sockets;
use Innmind\Immutable\{
    Set,
    Map,
};

final class Watch implements Command
{
    private Storage $watching;
    private Storage $notWatching;
    private Calendar $calendar;
    private Clock $clock;
    private Sockets $sockets;

    public function __construct(
        Storage $watching,
        Storage $notWatching,
        Calendar $calendar,
        Clock $clock,
        Sockets $sockets
    ) {
        $this->watching = $watching;
        $this->notWatching = $notWatching;
        $this->calendar = $calendar;
        $this->clock = $clock;
        $this->sockets = $sockets;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $shows = ($this->calendar)($this->clock->now())
            ->reduce(
                Set::of('string'),
                static function(Set $shows, Episode $episode): Set {
                    return $shows->add($episode->show());
                }
            )
            ->diff($this->watching->all()) // don't propose those already being wathed
            ->diff($this->notWatching->all()) // don't propose those already proposed
            ->reduce(
                Map::of('scalar', 'scalar'),
                static function(Map $shows, string $show): Map {
                    return $shows->put(
                        $shows->size(),
                        $show
                    );
                }
            );

        $ask = new ChoiceQuestion('Series to watch:', $shows);

        $toWatch = $ask($env, $this->sockets);

        $toWatch->foreach(function($key, string $show): void {
            $this->watching->add($show);
        });
        $shows
            ->values()
            ->diff($toWatch->values())
            ->foreach(function(string $show): void {
                $this->notWatching->add($show);
            });
    }

    public function toString(): string
    {
        return <<<USAGE
watch

Choose the series you want to watch

Will display a list of series airing this month, you'll need
to pick the ones you want to follow.

You'll need to run this command every month if you want to
follow new shows
USAGE;
    }
}
