<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use DateTimeZone;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;

/**
 * Factory for quick DateTimeAdapter instantiation.
 */
final class DateTimeAdapterFactory
{
    /**
     * @throws ConfigException
     * @throws Exception
     */
    public static function create(
        Type|string $fyType,
        DateTimeInterface|string $fyStartDate,
        bool $fiftyThreeWeeks = false,
        DateTimeZone|string|null $dateTimeZone = null
    ): DateTimeAdapter {
        return new DateTimeAdapter(
            fyType: $fyType,
            fyStartDate: $fyStartDate,
            fiftyThreeWeeks: $fiftyThreeWeeks,
            dateTimeZone: $dateTimeZone
        );
    }
}
