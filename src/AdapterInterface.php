<?php

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use Traversable;

/**
 * Interface AdapterInterface
 *
 * @package RoussKS\FinancialYear
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
     * Get the number of weeks for business type financial year or null for calendar type.
     *
     * @return int|null
     */
    public function getFyWeeks(): ?int;

    /**
     * Get the number of periods of the financial year.
     *
     * @return int
     */
    public function getFyPeriods(): int;

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business financial year type and will be set either 52 or 53.
     * Throw ConfigException for calendar type.
     *
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void;

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
     * Get the date range of the 53rd business week (if business type financial year & if exists, otherwise Exception).
     *
     * @return Traversable
     *
     * @throws ConfigException
     */
    public function getFiftyThirdBusinessWeek(): Traversable;

    /**
     * Get the start date of the next financial year.
     *
     * @return DateTimeInterface
     */
    public function getNextFyStartDate(): DateTimeInterface;

    /**
     * Validate configuration.
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function validateConfiguration(): void;
}