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
     * @var \DateTimeInterface
     */
    protected $financialYearAdapterType;

    /**
     * @var AdapterInterface
     */
    protected $financialYearAdapter;

    /**
     * FinancialYear constructor.
     *
     * @param  \DateTimeInterface $adapterType
     * @param  array|null $config = [
     *     'fyType'         => 'string', Enums\TypeEnum
     *     'fyStartDate'    => 'date', ISO-8601 format or adapter's object
     *     'fyEndDate'      => 'date', ISO-8601 format or adapter's object
     *     'fiftyThreeWeeks => 'bool', Applicable to business type financial year, if year has 52 or 53 weeks.
     * ]
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function __construct(\DateTimeInterface $adapterType, array $config = null)
    {
        $this->financialYearAdapterType = $adapterType;

        if ($config !== null) {
            $this->validateConfiguration($config);

            $this->financialYearAdapter = AdapterFactory::createAdapter($this->financialYearAdapterType, $config);
        }
    }

    /**
     * Instantiate the adapter if it hasn't already.
     *
     * @param  array $config
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function instantiateFinancialYearAdapter(array $config)
    {
        if ($this->financialYearAdapter !== null) {
            throw new Exception('The adapter has already been instantiated');
        }

        $this->validateConfiguration($config);

        $this->financialYearAdapter = AdapterFactory::createAdapter($this->financialYearAdapterType, $config);
    }

    /**
     * @return AdapterInterface
     *
     * @throws Exception
     */
    public function getFinancialYearAdapter()
    {
        if ($this->financialYearAdapter === null) {
            throw new Exception('The adapter has not been set yet');
        }

        return $this->financialYearAdapter;
    }

    /**
     * @param  array $config
     *
     * @return void
     *
     * @throws ConfigException
     */
    protected function validateConfiguration(array $config)
    {
        if (!isset($config['fyType']) || !is_string($config['fyType'])) {
            throw new ConfigException('The financial year type is required. Either \'calendar\' or \'business\'.');
        }

        if (!isset($config['fyStartDate'])) {
            throw new ConfigException('The financial year start date is required.');
        }
    }
}