<?php

namespace RoussKS\FinancialYear;

use RoussKS\FinancialYear\Exceptions\FinancialYearConfigException;
use RoussKS\FinancialYear\Interfaces\FinancialYearAdapterInterface;

class FinancialYear
{
    /** @var FinancialYearAdapterInterface  */
    public $adapter;

    /**
     * FinancialYear constructor.
     *
     * @param  null|FinancialYearAdapterInterface $adapter
     *
     * @return void
     *
     * @throws FinancialYearConfigException
     */
    public function __construct($adapter = null)
    {
        if ($adapter !== null) {

            $implementations = class_implements($adapter);

            if (!$implementations || !in_array(FinancialYearAdapterInterface::class, $implementations, true)) {
                throw new FinancialYearConfigException('This class does not implement the FinancialYearAdapterInterface');
            }

            $this->adapter = $adapter;

            return;
        }
    }

    /**
     * @return FinancialYearAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}