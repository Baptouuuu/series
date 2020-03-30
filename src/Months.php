<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\{
    PointInTime,
    Earth\Period\Month,
    Earth\Period\Day,
    Earth\Period\Hour,
    Earth\Period\Minute,
    Earth\Period\Second,
    Earth\Period\Millisecond,
};
use Innmind\Immutable\Set;

final class Months
{
    /**
     * @return Set<PointInTime>
     */
    public function __invoke(
        PointInTime $since,
        PointInTime $to
    ): Set {
        $since = $this->reset($since);
        $to = $this->reset($to);

        $months = Set::of(PointInTime::class);
        $month = $since;

        do {
            $months = $months->add($month);
            $month = $month->goForward(new Month(1));
        } while ($to->aheadOf($month));

        if (!$since->equals($to)) { // when spanning multiple months
            $months = $months->add($to);
        }

        return $months;
    }

    /**
     * Move the point to the start of the month so we can safely move month to month
     */
    private function reset(PointInTime $time): PointInTime
    {
        return $time->goBack(
            (new Day($time->day()->toInt() - 1))
                ->add(new Hour($time->hour()->toInt()))
                ->add(new Minute($time->minute()->toInt()))
                ->add(new Second(max($time->second()->toInt() - 1, 0)))
                ->add(new Millisecond($time->millisecond()->toInt()))
        );
    }
}
