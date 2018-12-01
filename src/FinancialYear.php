<?php

namespace RoussKS\FinancialYear;

use RoussKS\FinancialYear\Exceptions\ConfigException;
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
     *     'adapter'     => 'string', any of the library's supported adapters
     *     'type'        => 'string', calendar or business
     *     'fyStartDate' => 'date', ISO-8601 format or adapter's object
     *     'fyEndDate'   => 'date', ISO-8601 format or adapter's object
     * ]
     * @param  null|AdapterInterface $adapter
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function __construct(array $config = [], AdapterInterface $adapter = null)
    {
        if ($adapter !== null) {

            $this->adapter = $adapter;

            return;
        }

        if (!isset($config['adapter'])) {
            throw new ConfigException('The adapter configuration key is required.');
        }
    }
}