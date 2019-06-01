<?php

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use RoussKS\FinancialYear\Adapters\DateTimeAdapter;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Adapters\AdapterInterface;

/**
 * Class FinancialYear
 *
 * Handles relevant financial year adapter instantiation (acts like a factory).
 *
 * @package RoussKS\FinancialYear
 */
class FinancialYear
{
    /**
     * @var DateTimeInterface
     */
    protected $adapterType;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * FinancialYear constructor.
     *
     * @param  DateTimeInterface $adapterType
     * @param  array|null $config = [
     *     'fyType'         => 'string', `calendar` or `business` as set in the AbstractAdapter Constants.
     *     'fyStartDate'    => 'date', ISO-8601 format or adapter's object
     *     'fiftyThreeWeeks => 'bool', Applicable to business type financial year, if year has 52 or 53 weeks.
     * ]
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function __construct(DateTimeInterface $adapterType, array $config = null)
    {
        $this->adapterType = $adapterType;

        // If a config is provided, instantiate the relevant adapter.
        if ($config !== null) {
            $this->makeFinancialYearAdapter($config);
        }
    }

    /**
     * Instantiate the adapter if it hasn't been instantiated already.
     *
     * @param  array $config = [
     *     'fyType'         => 'string', `calendar` or `business`
     *     'fyStartDate'    => 'date', ISO-8601 format or adapter's object
     *     'fiftyThreeWeeks => 'bool', Applicable to business type financial year, if year has 52 or 53 weeks.
     * ]
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function makeFinancialYearAdapter(array $config): void
    {
        // Throw exception for improper use (multiple instantiations).
        if ($this->adapter !== null) {
            throw new Exception('The adapter has already been instantiated');
        }

        $this->validateConfiguration($config);

        // Switch on fully qualified class name.
        switch (get_class($this->adapterType)) {
            case 'DateTime':
            case 'DateTimeImmutable':
                $this->adapter = new DateTimeAdapter(
                    $config['fyType'],
                    $config['fyStartDate'],
                    $config['fiftyThreeWeeks'] ?? false
                );
                break;
            default:
                throw new ConfigException('Unknown adapter configuration key');
        }
    }

    /**
     * @return AdapterInterface
     *
     * @throws Exception
     */
    public function getAdapter(): AdapterInterface
    {
        if ($this->adapter === null) {
            throw new Exception('The adapter has not been set yet');
        }

        return $this->adapter;
    }

    /**
     * @param  array $config
     *
     * @return void
     *
     * @throws ConfigException
     */
    protected function validateConfiguration(array $config): void
    {
        if (!isset($config['fyType']) || !is_string($config['fyType'])) {
            throw new ConfigException('The financial year type is required. Either \'calendar\' or \'business\'.');
        }

        if (!isset($config['fyStartDate'])) {
            throw new ConfigException('The financial year start date is required.');
        }
    }
}