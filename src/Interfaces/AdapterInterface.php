<?php

namespace RoussKS\FinancialYear\Interfaces;

use RoussKS\Enums\TypeEnum;
use RoussKS\FinancialYear\Exceptions\ConfigException;

/**
 * Interface AdapterInterface
 *
 * @package RoussKS\FinancialYear\Interfaces
 */
interface AdapterInterface
{
    /**
     * Get the financial year's type.
     *
     * @return TypeEnum
     */
    public function getType();

    /**
     * Get the financial year's start date.
     *
     * @return mixed
     */
    public function getFyStartDate();

    /**
     * Set the financial year's start date.
     *
     * Expects either string ISO-8601 format 'YYYY-MM-DD'
     * or a date object, same object instance as the adapter's
     *
     * @param  mixed $date
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyStartDate($date);

    /**
     * Get the financial year's end date.
     *
     * @return mixed
     */
    public function getFyEndDate();

    /**
     * Set the financial year's end date.
     *
     * Expects either string ISO-8601 format 'YYYY-MM-DD'
     * or a date object, same object instance as the adapter's
     * or null where the adapter sets itself according the start date.
     *
     * @param  mixed $date
     * @param  bool $fiftyThreeWeeks // applicable to Financial Year TypeEnum 'business'
     *
     * @return void
     */
    public function setFyEndDate($date = null, $fiftyThreeWeeks = false);

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getPeriodId();

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getBusinessWeekId();

    /**
     * @return void
     *
     * @throws ConfigException
     */
    public function validate();
}