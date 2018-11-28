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
     *     'adapter'   => 'string', any of the library's supported adapters
     *     'type'      => 'string', calendar or business
     *     'startDate' => 'date', YYYY-MM-DD format
     *     'endDate'   => 'date', YYYY-MM-DD format
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