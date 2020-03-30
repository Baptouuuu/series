<?php
declare(strict_types = 1);

namespace Series\Storage;

use Series\Storage;
use Innmind\Filesystem\{
    Adapter,
    File,
    Stream\NullStream,
    Stream\StringStream,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
    Str,
};

final class Filesystem implements Storage
{
    private Adapter $filesystem;
    private String $file;

    public function __construct(Adapter $adapter, string $file)
    {
        $this->filesystem = $adapter;
        $this->file = $file;
    }

    public function add(string $series): Storage
    {
        $file = $this->open();
        $this->save(new File\File(
            (string) $file->name(),
            new StringStream(
                (string) Str::of("%s\n%s")
                    ->sprintf(
                        (string) $file->content(),
                        $series
                    )
                    ->trim()
            )
        ));

        return $this;
    }

    public function remove(string $series): Storage
    {
        $file = $this->open();
        $this->save(new File\File(
            (string) $file->name(),
            new StringStream(
                (string) Str::of((string) $file->content())
                    ->split("\n")
                    ->filter(static function(Str $line) use ($series): bool {
                        return (string) $line !== $series;
                    })
                    ->join("\n")
            )
        ));

        return $this;
    }

    /**
     * @return SetInterface<string>
     */
    public function all(): SetInterface
    {
        return Str::of((string) $this->open()->content())
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->empty();
            })
            ->reduce(
                Set::of('string'),
                static function(Set $series, Str $line): Set {
                    return $series->add((string) $line);
                }
            );
    }

    private function open(): File
    {
        if (!$this->filesystem->has($this->file)) {
            return new File\File($this->file, new NullStream);
        }

        return $this->filesystem->get($this->file);
    }

    private function save(File $file): void
    {
        $this->filesystem->add($file);
    }
}
