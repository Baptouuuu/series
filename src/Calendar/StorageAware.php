<?php
declare(strict_types = 1);

namespace Series\Calendar;

use Series\{
    Calendar,
    Episode,
    Storage,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Set;

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
    public function __invoke(PointInTime $month): Set
    {
        $shows = $this->watching->all();

        return ($this->calendar)($month)->filter(static function(Episode $episode) use ($shows): bool {
            return $shows->contains($episode->show());
        });
    }
}
