<?php

namespace App\Enum;

enum IntervalUnit: string
{
    case DAY = 'd';
    case WEEK = 'w';
    case MONTH = 'm';
    case YEAR = 'y';

    public function toString(): string
    {
        return match ($this) {
            self::DAY => __('ui.days'),
            self::WEEK => __('ui.weeks'),
            self::MONTH => __('ui.months'),
            self::YEAR => __('ui.years'),
        };
    }
}
