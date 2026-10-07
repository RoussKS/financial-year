<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

enum Type: string
{
    case CALENDAR = 'calendar';
    case BUSINESS = 'business';

    public function isCalendar(): bool
    {
        return $this === self::CALENDAR;
    }

    public function isBusiness(): bool
    {
        return $this === self::BUSINESS;
    }

    public function isNotBusiness(): bool
    {
        return $this->isBusiness() === false;
    }
}
