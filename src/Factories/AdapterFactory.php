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
     * @param  array $config
     *
     * @return AdapterInterface
     *
     * @throws ConfigException
     * @throws \Exception
     */
    public static function createAdapter(array $config = [])
    {
        if (!isset($config['adapter'])) {
            throw new ConfigException('The adapter configuration key is required.');
        }

        switch ($config['adapter']) {
            case 'datetime':
                return new DateTimeAdapter(
                    new \DateTime(),
                    isset($config['type']) ? $config['type'] : null,
                    isset($config['startDate']) ? $config['startDate'] : null,
                    isset($config['endDate']) ? $config['endDate'] : null
                );
            default:
                throw new ConfigException('Unknown adapter configuration key');
        }
    }
}