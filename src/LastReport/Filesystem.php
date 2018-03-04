<?php
declare(strict_types = 1);

namespace Series\LastReport;

use Series\{
    LastReport,
    Exception\RuntimeException,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface,
    Format\ISO8601,
};
use Innmind\Filesystem\{
    Adapter,
    File\File,
    Stream\StringStream,
};

final class Filesystem implements LastReport
{
    private $filesystem;
    private $file;
    private $clock;

    public function __construct(
        Adapter $filesystem,
        string $file,
        TimeContinuumInterface $clock
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->clock = $clock;
    }

    public function at(PointInTimeInterface $time): LastReport
    {
        $this->filesystem->add(new File(
            $this->file,
            new StringStream($time->format(new ISO8601))
        ));

        return $this;
    }

    public function when(): PointInTimeInterface
    {
        if (!$this->filesystem->has($this->file)) {
            throw new RuntimeException;
        }

        return $this->clock->at(
            (string) $this->filesystem->get($this->file)->content()
        );
    }
}
