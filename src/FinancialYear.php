<?php

namespace RoussKS\FinancialYear;

use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Factories\AdapterFactory;
use RoussKS\FinancialYear\Interfaces\AdapterInterface;

/**
 * Class FinancialYear
 *
 * @package RoussKS\FinancialYear
 */
class FinancialYear
{
    /** @var AdapterInterface  */
    public $adapter;

    /**
     * FinancialYear constructor.
     *
     * @param  array $config = [
     *     'fyType'      => 'string', Enums\TypeEnum
     *     'fyStartDate' => 'date', ISO-8601 format or adapter's object
     *     'fyEndDate'   => 'date', ISO-8601 format or adapter's object
     * ]
     * @param  \DateTimeInterface $adapterType
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function __construct(
        \DateTimeInterface $adapterType, array $config = []
    ) {
        if (!isset($config['fyType'])) {
            throw new ConfigException('The financial year type is required.');
        }

        $this->adapter = AdapterFactory::createAdapter($adapterType);
    }
}