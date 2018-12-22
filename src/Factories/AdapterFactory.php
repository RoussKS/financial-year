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
     * @param  mixed $adapterType
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
                    $adapterType,
                    isset($config['fyType']) ? $config['fyType'] : null,
                    isset($config['fyStartDate']) ? $config['fyStartDate'] : null,
                    isset($config['fyEndDate']) ? $config['fyEndDate'] : null
                );
            default:
                throw new ConfigException('Unknown adapter configuration key');
        }
    }
}