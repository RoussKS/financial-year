<?php

namespace RoussKS\FinancialYear\Enums;

/**
 * Class TypeEnum
 *
 * @package RoussKS\FinancialYear\Enums
 */
class TypeEnum
{
    /**
     * The financial year calendar type constant.
     */
    public const TYPE_CALENDAR = 'calendar';

    /**
     * The financial year business type constant.
     */
    public const TYPE_BUSINESS = 'business';

    /**
     * Check if calendar type financial year.
     *
     * @param  string $value
     *
     * @return bool
     */
    public static function isCalendar(string $value): bool
    {
        return $value === self::TYPE_CALENDAR;
    }

    /**
     * Check if business type financial year.
     *
     * @param  string $value
     *
     * @return bool
     */
    public static function isBusiness(string $value): bool
    {
        return $value === self::TYPE_CALENDAR;
    }
}