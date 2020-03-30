<?php
declare(strict_types = 1);

namespace Series\Calendar;

use Series\{
    Calendar,
    Episode,
    Storage,
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\SetInterface;

final class StorageAware implements Calendar
{
    private Calendar $calendar;
    private Storage $watching;

    public function __construct(Calendar $calendar, Storage $watching)
    {
        $this->calendar = $calendar;
        $this->watching = $watching;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(PointInTimeInterface $month): SetInterface
    {
        $shows = $this->watching->all();

        return ($this->calendar)($month)->filter(static function(Episode $episode) use ($shows): bool {
            return $shows->contains($episode->show());
        });
    }
}
