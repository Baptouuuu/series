<?php
declare(strict_types = 1);

namespace Series\LastReport;

use Series\{
    LastReport,
    Exception\RuntimeException,
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
    Earth\Format\ISO8601,
};
use Innmind\Filesystem\{
    Adapter,
    File\File,
    Name,
};
use Innmind\Stream\Readable\Stream;

final class Filesystem implements LastReport
{
    private Adapter $filesystem;
    private Name $file;
    private Clock $clock;

    public function __construct(
        Adapter $filesystem,
        string $file,
        Clock $clock
    ) {
        $this->filesystem = $filesystem;
        $this->file = new Name($file);
        $this->clock = $clock;
    }

    public function at(PointInTime $time): LastReport
    {
        $this->filesystem->add(new File(
            $this->file,
            Stream::ofContent($time->format(new ISO8601))
        ));

        return $this;
    }

    public function when(): PointInTime
    {
        if (!$this->filesystem->contains($this->file)) {
            throw new RuntimeException;
        }

        return $this->clock->at(
            $this->filesystem->get($this->file)->content()->toString()
        );
    }
}
