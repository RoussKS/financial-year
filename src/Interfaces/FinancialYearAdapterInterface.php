<?php

namespace RoussKS\FinancialYear\Interfaces;

use RoussKS\FinancialYear\Exceptions\FinancialYearConfigException;

interface FinancialYearAdapterInterface
{
    /**
     * @param  string $type
     *
     * @return void
     * 
     * @throws FinancialYearConfigException
     */
    public function setType($type);

    /**
     * @return void
     *
     * @throws FinancialYearConfigException
     */
    public function validate();

    /**
     * @param  mixed $date
     *
     * @return mixed
     */
    public function setStartOfTheYear($date);

    /**
     * @return int
     *
     * @throws FinancialYearConfigException
     */
    public function getDayTypePeriodId();

    /**
     * @return int
     *
     * @throws FinancialYearConfigException
     */
    public function getDayTypeWeekId();
}