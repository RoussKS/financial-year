<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;

/***
 * Class AdapterFactory
 *
 * @package RoussKS\FinancialYear\Factories
 */
class AdapterFactory
{
    /**
     * @param  \DateTimeInterface $adapterType
     * @param  array $config = [
     *     'fyType'         => 'string', Enums\TypeEnum
     *     'fyStartDate'    => 'date', ISO-8601 format or adapter's object
     *     'fyEndDate'      => 'date', ISO-8601 format or adapter's object
     *     'fiftyThreeWeeks => 'bool', Applicable to business type financial year, if year has 52 or 53 weeks.
     * ]
     *
     * @return AdapterInterface
     *
     * @throws Exception
     * @throws ConfigException
     */
    public static function createAdapter(\DateTimeInterface $adapterType, array $config): ?AdapterInterface
    {
        // Switch on fully qualified class name.
        switch (get_class($adapterType)) {
            case 'DateTime':
            case 'DateTimeImmutable':
                return new DateTimeAdapter(
                    $config['fyType'],
                    $config['fyStartDate'],
                    $config['fiftyThreeWeeks'] ?? false,
                    $config['fyEndDate'] ?? null
                );
            default:
                throw new ConfigException('Unknown adapter configuration key');
        }
    }
}