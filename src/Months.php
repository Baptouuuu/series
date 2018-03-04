<?php
declare(strict_types = 1);

namespace Series;

use Innmind\TimeContinuum\{
    PointInTimeInterface,
    Period\Earth\Month,
    Period\Earth\Day,
    Period\Earth\Hour,
    Period\Earth\Minute,
    Period\Earth\Second,
    Period\Earth\Millisecond,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Months
{
    /**
     * @return SetInterface<PointInTimeInterface>
     */
    public function __invoke(
        PointInTimeInterface $since,
        PointInTimeInterface $to
    ): SetInterface {
        $since = $this->reset($since);
        $to = $this->reset($to);

        $months = Set::of(PointInTimeInterface::class);
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
    private function reset(PointInTimeInterface $time): PointInTimeInterface
    {
        return $time->goBack(
            (new Day($time->day()->toInt() - 1))
                ->add(new Hour($time->hour()->toInt()))
                ->add(new Minute($time->minute()->toInt()))
                ->add(new Second($time->second()->toInt() - 1))
                ->add(new Millisecond($time->millisecond()->toInt()))
        );
    }
}
