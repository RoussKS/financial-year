<?php

namespace RoussKS\FinancialYear;

use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Factories\AdapterFactory;
use RoussKS\FinancialYear\Interfaces\AdapterInterface;

/**
 * Class FinancialYear
 *
 * @package RoussKS\FinancialYear
 */
class FinancialYear
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * FinancialYear constructor.
     *
     * @param  \DateTimeInterface $adapterType
     * @param  array $config = [
     *     'fyType'         => 'string', Enums\TypeEnum
     *     'fyStartDate'    => 'date', ISO-8601 format or adapter's object
     *     'fyEndDate'      => 'date', ISO-8601 format or adapter's object
     *     'fiftyThreeWeeks => 'bool', Applicable to business type financial year, if year has 52 or 53 weeks.
     * ]
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function __construct(\DateTimeInterface $adapterType, array $config)
    {
        if (!isset($config['fyType']) || !is_string($config['fyType'])) {
            throw new ConfigException('The financial year type is required. Either \'calendar\' or \'business\'.');
        }

        if (!isset($config['fyStartDate'])) {
            throw new ConfigException('The financial year start date is required.');
        }

        $this->adapter = AdapterFactory::createAdapter($adapterType, $config);
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}