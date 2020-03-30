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
use Innmind\Immutable\Map;

final class Unwatch implements Command
{
    private Storage $watching;
    private Storage $notWatching;

    public function __construct(Storage $watching, Storage $notWatching)
    {
        $this->watching = $watching;
        $this->notWatching = $notWatching;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $shows = $this
            ->watching
            ->all()
            ->reduce(
                new Map('scalar', 'scalar'),
                static function(Map $shows, string $show): Map {
                    return $shows->put(
                        $shows->size(),
                        $show
                    );
                }
            );

        $ask = new ChoiceQuestion('Shows to stop watching:', $shows);
        $choices = $ask($env->input(), $env->output());

        $choices->foreach(function($key, string $show): void {
            $this->watching->remove($show);
            $this->notWatching->add($show);
        });
    }

    public function __toString(): string
    {
        return <<<USAGE
unwatch

Choose the series you want to stop watching
USAGE;
    }
}
