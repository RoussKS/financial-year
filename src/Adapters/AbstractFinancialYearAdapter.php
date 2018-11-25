<?php

namespace RoussKS\FinancialYear\Adapters;

abstract class AbstractFinancialYearAdapter
{
    /**
     * @var \RoussKS\FinancialYear\Interfaces\FinancialYearAdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $type;
}