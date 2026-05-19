<?php

namespace RoussKS\FinancialYear;

use DateTime;
use DateTimeImmutable;
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
     * @param string $fyType
     * @param DateTime|DateTimeImmutable|DateTimeInterface|string $fyStartDate
     * @param bool $fiftyThreeWeeks
     * @param DateTimeZone|string|null $dateTimeZone
     *
     * @throws ConfigException
     * @throws Exception
     */
    public static function create(
        string $fyType,
        $fyStartDate,
        bool $fiftyThreeWeeks = false,
        $dateTimeZone = null
    ): DateTimeAdapter {
        return new DateTimeAdapter($fyType, $fyStartDate, $fiftyThreeWeeks, $dateTimeZone);
    }
}
