<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use Traversable;

interface AdapterInterface
{
    /**
     * Get the financial year type.
     */
    public function getType(): string;

    /**
     * Get the number of weeks for business type financial year or null for calendar type.
     */
    public function getFyWeeks(): ?int;

    /**
     * Get the number of periods of the financial year.
     */
    public function getFyPeriods(): int;

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business financial year type and will be set either 52 or 53.
     * Throw ConfigException for calendar type.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void;

    /**
     * Get the financial year start date.
     */
    public function getFyStartDate(): DateTimeInterface;

    /**
     * Set the financial year start date.
     *
     * Expects either string ISO-8601 format 'YYYY-MM-DD'
     * or a date object, same object instance as the adapter's that extends the DateTimeInterface
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function setFyStartDate(DateTimeInterface|string $date): void;

    /**
     * Get the financial year's end date.
     */
    public function getFyEndDate(): DateTimeInterface;

    /**
     * Get the date range of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getPeriodById(int $id): Traversable;

    /**
     * Get the date range of the business week with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getBusinessWeekById(int $id): Traversable;

    /**
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getPeriodIdByDate(DateTimeInterface|string $date): int;

    /**
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getBusinessWeekIdIdByDate(DateTimeInterface|string $date): int;

    /**
     * Get the first date of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeInterface;

    /**
     * Get the last date of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getLastDateOfPeriodById(int $id): DateTimeInterface;

    /**
     * Get the first date of the business week with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getFirstDateOfBusinessWeekById(int $id): DateTimeInterface;

    /**
     * Get the last date of the business week with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getLastDateOfBusinessWeekById(int $id): DateTimeInterface;

    /**
     * Get the date range of the first business week of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getFirstBusinessWeekByPeriodId(int $id): Traversable;

    /**
     * Get the date range of the second business week of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getSecondBusinessWeekByPeriodId(int $id): Traversable;

    /**
     * Get the date range of the third business week of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getThirdBusinessWeekOfPeriodId(int $id): Traversable;

    /**
     * Get the date range of the fourth business week of the period with the given id.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getFourthBusinessWeekByPeriodId(int $id): Traversable;

    /**
     * Get the date range of the 53rd business week (if business type financial year & if exists, otherwise Exception).
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function getFiftyThirdBusinessWeek(): Traversable;

    /**
     * Get the start date of the next financial year.
     */
    public function getNextFyStartDate(): DateTimeInterface;

    /**
     * Validate configuration.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     */
    public function validateConfiguration(): void;
}
