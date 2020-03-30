<?php
declare(strict_types = 1);

namespace Series\Command;

use Series\Storage;
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
    Question\ChoiceQuestion,
};
use Innmind\OperatingSystem\Sockets;
use Innmind\Immutable\Map;

final class Unwatch implements Command
{
    private Storage $watching;
    private Storage $notWatching;
    private Sockets $sockets;

    public function __construct(
        Storage $watching,
        Storage $notWatching,
        Sockets $sockets
    ) {
        $this->watching = $watching;
        $this->notWatching = $notWatching;
        $this->sockets = $sockets;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $shows = $this
            ->watching
            ->all()
            ->reduce(
                Map::of('scalar', 'scalar'),
                static function(Map $shows, string $show): Map {
                    return $shows->put(
                        $shows->size(),
                        $show
                    );
                }
            );

        $ask = new ChoiceQuestion('Shows to stop watching:', $shows);
        $choices = $ask($env, $this->sockets);

        $choices->foreach(function($key, string $show): void {
            $this->watching->remove($show);
            $this->notWatching->add($show);
        });
    }

    public function toString(): string
    {
        return <<<USAGE
unwatch

Choose the series you want to stop watching
USAGE;
    }
}
