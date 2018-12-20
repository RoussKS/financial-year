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
     * or a date object, same object instance as the adapter's that extends the DateTimeInterface
     *
     * Throws an exception if FyEndDate is already set.
     *
     * @param  string|\DateTimeInterface $date
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
     * or a date object, same object instance as the adapter's that extends the DateTimeInterface
     *
     * Throws an exception if FyEndDate is already set.
     *
     * @param  string|\DateTimeInterface $date
     * @param  bool $fiftyThreeWeeks // applicable to Financial Year TypeEnum 'business'
     *
     * @return void
     */
    public function setFyEndDate($date, $fiftyThreeWeeks = false);

    /**
     * @param  \DateTimeInterface $startDate
     * @param  \DateTimeInterface $endDate
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getPeriodIdByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate);

    /**
     * @param  \DateTimeInterface $startDate
     * @param  \DateTimeInterface $endDate
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getBusinessWeekIdByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate);

    /**
     * Get the date range of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getPeriodById(int $id);

    /**
     * Get the date range of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getBusinessWeekById(int $id);

    /**
     * Get the first date of the period with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getFirstDateOfPeriodById(int $id);

    /**
     * Get the last date of the period with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getLastDateOfPeriodById(int $id);

    /**
     * Get the first date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getFirstDateOfBusinessWeekById(int $id);

    /**
     * Get the last date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getLastDateOfBusinessWeekById(int $id);

    /**
     * Get the date range of the first business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getFirstBusinessWeekByPeriodId(int $id);

    /**
     * Get the date range of the second business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getSecondBusinessWeekByPeriodId(int $id);

    /**
     * Get the date range of the third business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getThirdBusinessWeekOfPeriodId(int $id);

    /**
     * Get the date range of the fourth business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getFourthBusinessWeekByPeriodId(int $id);

    /**
     * @return void
     *
     * @throws ConfigException
     */
    public function validate();
}