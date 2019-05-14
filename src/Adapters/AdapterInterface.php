<?php

namespace RoussKS\FinancialYear\Adapters;

use DateTimeInterface;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use Traversable;

/**
 * Interface AdapterInterface
 *
 * @package RoussKS\FinancialYear\Interfaces
 */
interface AdapterInterface
{
    /**
     * Get the financial year type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * @return int|null
     */
    public function getFyWeeks(): ?int;

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business financial year type and will be set either 52 or 53.
     *
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyWeeks($fiftyThreeWeeks = false): void;

    /**
     * Get the financial year start date.
     *
     * @return DateTimeInterface
     */
    public function getFyStartDate(): DateTimeInterface;

    /**
     * Set the financial year start date.
     *
     * Expects either string ISO-8601 format 'YYYY-MM-DD'
     * or a date object, same object instance as the adapter's that extends the DateTimeInterface
     *
     * Throws an exception if FyEndDate is already set.
     *
     * @param  string|DateTimeInterface $date
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyStartDate($date): void;

    /**
     * Get the financial year end date.
     *
     * @return DateTimeInterface
     */
    public function getFyEndDate(): DateTimeInterface;

    /**
     * Set the financial year end date.
     *
     * Expects either string ISO-8601 format 'YYYY-MM-DD' or a date object,
     * same object instance as the adapter's that extends the DateTimeInterface.
     *
     * @param  string|DateTimeInterface $date
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyEndDate($date): void;

    /**
     * Get the date range of the period with the given id.
     *
     * @param  int $id
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getPeriodById(int $id): Traversable;

    /**
     * Get the date range of the business week with the given id.
     *
     * @param  int $id
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getBusinessWeekById(int $id): Traversable;

    /**
     * @param  string|DateTimeInterface $date
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getPeriodIdByDate($date): int;

    /**
     * @param  string|DateTimeInterface $date
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getBusinessWeekIdIdByDate($date): int;

    /**
     * Get the first date of the period with the given id.
     *
     * @param  int $id
     *
     * @return DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeInterface;

    /**
     * Get the last date of the period with the given id.
     *
     * @param  int $id
     *
     * @return DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getLastDateOfPeriodById(int $id): DateTimeInterface;

    /**
     * Get the first date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getFirstDateOfBusinessWeekById(int $id): DateTimeInterface;

    /**
     * Get the last date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getLastDateOfBusinessWeekById(int $id): DateTimeInterface;

    /**
     * Get the date range of the first business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getFirstBusinessWeekByPeriodId(int $id): Traversable;

    /**
     * Get the date range of the second business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getSecondBusinessWeekByPeriodId(int $id): Traversable;

    /**
     * Get the date range of the third business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getThirdBusinessWeekOfPeriodId(int $id): Traversable;

    /**
     * Get the date range of the fourth business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getFourthBusinessWeekByPeriodId(int $id): Traversable;

    /**
     * @return void
     *
     * @throws ConfigException
     */
    public function validate(): void;
}