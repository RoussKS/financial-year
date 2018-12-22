<?php

namespace RoussKS\FinancialYear\Factories;

use RoussKS\FinancialYear\Adapters\DateTimeAdapter;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Interfaces\AdapterInterface;

/***
 * Class AdapterFactory
 *
 * @package RoussKS\FinancialYear\Factories
 */
class AdapterFactory
{
    /**
     * @param  mixed $adapterType, \DateTime currently supported.
     * @param  array $config
     *
     * @return AdapterInterface
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public static function createAdapter($adapterType, array $config = [])
    {
        if (!isset($config['adapter'])) {
            throw new ConfigException('The adapter configuration key is required.');
        }

        switch (get_class($adapterType)){
            case '\DateTime':
                return new DateTimeAdapter(
                    $config['fyType'],
                    $config['fyStartDate'],
                    isset($config['fyEndDate']) ? $config['fyEndDate'] : null,
                    isset($config['fiftyThreeWeeks']) ? $config['fiftyThreeWeeks'] : false
                );
            default:
                throw new ConfigException('Unknown adapter configuration key');
        }
    }
}