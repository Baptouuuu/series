<?php
declare(strict_types = 1);

namespace Series\Storage;

use Series\Storage;
use Innmind\Filesystem\{
    Adapter,
    File,
    Stream\NullStream,
    Name,
};
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\{
    Set,
    Str,
};
use function Innmind\Immutable\join;

final class Filesystem implements Storage
{
    private Adapter $filesystem;
    private Name $file;

    public function __construct(Adapter $adapter, string $file)
    {
        $this->filesystem = $adapter;
        $this->file = new Name($file);
    }

    public function add(string $series): Storage
    {
        $file = $this->open();
        $this->save(new File\File(
            $file->name(),
            Stream::ofContent(
                Str::of("%s\n%s")
                    ->sprintf(
                        $file->content()->toString(),
                        $series
                    )
                    ->trim()
                    ->toString()
            )
        ));

        return $this;
    }

    public function remove(string $series): Storage
    {
        $file = $this->open();
        $this->save(new File\File(
            $file->name(),
            Stream::ofContent(
                join(
                    "\n",
                    Str::of($file->content()->toString())
                        ->split("\n")
                        ->filter(static function(Str $line) use ($series): bool {
                            return $line->toString() !== $series;
                        })
                        ->mapTo(
                            'string',
                            static fn(Str $line): string => $line->toString(),
                        )
                )->toString()
            )
        ));

        return $this;
    }

    /**
     * @return Set<string>
     */
    public function all(): Set
    {
        return Str::of($this->open()->content()->toString())
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->empty();
            })
            ->reduce(
                Set::of('string'),
                static function(Set $series, Str $line): Set {
                    return $series->add($line->toString());
                }
            );
    }

    private function open(): File
    {
        if (!$this->filesystem->contains($this->file)) {
            return new File\File($this->file, new NullStream);
        }

        return $this->filesystem->get($this->file);
    }

    private function save(File $file): void
    {
        $this->filesystem->add($file);
    }
}
