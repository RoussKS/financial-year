<?php

namespace RoussKS\FinancialYear\Adapters;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use Traversable;

/**
 * Implementation of PHP DateTime FinancialYear Adapter
 *
 * Class DateTimeAdapter
 *
 * @package RoussKS\FinancialYear\Adapters
 */
class DateTimeAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * @var DateTimeInterface|DateTimeImmutable
     */
    protected $fyStartDate;

    /**
     * @var DateTimeInterface|DateTimeImmutable
     */
    protected $fyEndDate;

    /**
     * DateTimeAdapter constructor.
     *
     * $fyEndDate, if provided, has priority and overrides $fiftyThreeWeeks for 'business' $fyType.
     *
     * @param  string $fyType
     * @param  DateTime|DateTimeImmutable|string $fyStartDate
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function __construct(
        string $fyType,
        $fyStartDate,
        bool $fiftyThreeWeeks = false
    ) {
        parent::__construct($fyType, $fiftyThreeWeeks);

        $this->setFyStartDate($fyStartDate);

        $this->setFyEndDate();
    }

    /**
     * {@inheritdoc}
     *
     * Extend parent class in order to recalculate end date if the business year weeks change.
     *
     * @throws Exception
     */
    public function setFyWeeks($fiftyThreeWeeks = false): void
    {
        $originalFyWeeks = $this->fyWeeks;

        parent::setFyWeeks($fiftyThreeWeeks);

        // Reset the financial year end date according to the weeks setting.
        if ($originalFyWeeks !== null && $originalFyWeeks !== $this->fyWeeks) {
            $this->setFyEndDate();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTimeInterface|DateTimeImmutable
     */
    public function getFyStartDate(): DateTimeInterface
    {
        return $this->fyStartDate;
    }

    /**
     * {@inheritdoc}
     *
     * @param  DateTime|DateTimeImmutable|string $date
     *
     * @throws Exception
     */
    public function setFyStartDate($date): void
    {
        // fyStartDate property is an immutable object.
        $originalFyStartDate = $this->fyStartDate;

        $this->fyStartDate = $this->getDateObject($date);

        if ($this->fyStartDate->format('md') === '0229' && $this->isCalendarType($this->type)) {
            $this->throwConfigurationException('This library does not support 29th of February as the starting date for calendar type financial year');
        }

        // If this method was not called on instantiation,
        // recalculate financial year end date from current settings,
        // even if the new start date is the same as the previous one (why re-setting the same date?).
        if ($originalFyStartDate !== null) {
            $this->setFyEndDate();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTimeInterface|DateTimeImmutable
     */
    public function getFyEndDate(): DateTimeInterface
    {
        return $this->fyEndDate;
    }

    /**
     * {@inheritdoc}
     *
     * First check for calendar type and the return the corresponding value.
     * Otherwise it is business type as the only other available.
     *
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws Exception
     * @throws \Exception
     */
    public function getPeriodById(int $id): Traversable
    {
        $this->validate();

        $this->validatePeriodId($id);

        // Set default values for more readable logic within conditions (for first and last fyPeriods).
        // Financial Year start date is the first period's start date.
        // Financial Year end date is the last period's end date.
        $periodStartDate = $this->fyStartDate;
        $periodEndDate = $this->fyEndDate;

        /*
         * Calendar Type
         *
         * In calendar type, fyPeriods are always 12 as the months, regardless of the start date within the month.
         */
        if ($this->isCalendarType($this->type)) {
            // If first period, period start date is the financial year start date.
            // If not the first period, calculate the correct date.
            if ($id !== 1) {
                $periodStartDate = $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' months'));
            }

            // If last period, period last date is is the financial year end date.
            // If not last period, it's the end of the month.
            if ($id !== $this->fyPeriods) {
                $periodEndDate = $periodStartDate->add(DateInterval::createFromDateString('1 month'))
                                                 ->sub(DateInterval::createFromDateString('1 day'));
            }

            return new DatePeriod($periodStartDate, DateInterval::createFromDateString('1 day'), $periodEndDate);
        }

        // If first period, period start date is the financial year start date.
        // If not the first period, calculate the correct date
        if ($id !== 1) {
            $periodStartDate = $this->fyStartDate->add(DateInterval::createFromDateString(($id - 1) * 4 . ' weeks'));
        }

        // If last period (13 for business type), period last date is is the financial year end date.
        // This way we also overcome the potential issue of a 53rd week.
        // If not last period, it's the end of the month.
        if ($id !== $this->fyPeriods) {
            $periodEndDate = $periodStartDate->add(DateInterval::createFromDateString('4 weeks'))
                                             ->sub(DateInterval::createFromDateString('1 day'));
        }

        return new DatePeriod($periodStartDate, DateInterval::createFromDateString('1 day'), $periodEndDate);
    }

    /**
     * {@inheritdoc}
     *
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws Exception
     */
    public function getBusinessWeekById(int $id): Traversable
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // Set default values.
        // Will be used if first or last week.
        $weekStartDate = $this->fyStartDate;
        $weekEndDate = $this->fyEndDate;

        // If not the first week, calculate period start date.
        if ($id !== 1) {
            $weekStartDate = $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' weeks'));
        }

        // If not last week of the year, calculate period end date from start date.
        if ($id !== $this->fyWeeks) {
            $weekEndDate = $weekStartDate->add(DateInterval::createFromDateString('6 days'));
        }

        return new DatePeriod($weekStartDate, DateInterval::createFromDateString('1 day'), $weekEndDate);
    }

    /**
     * {@inheritdoc}
     *
     * @param  DateTime|DateTimeImmutable|string $date
     *
     * @throws Exception
     */
    public function getPeriodIdByDate($date): int
    {
        $dateTime = $this->getDateObject($date);

        // Instantly throw exception for a date that's out of range of the current financial year.
        // Do this to avoid the resource intensive loop.
        if ($dateTime < $this->fyStartDate || $dateTime > $this->fyEndDate) {
            throw new Exception('The requested date is out of range of the current financial year');
        }

        for ($id = 1; $id <= $this->fyPeriods; $id++) {
            /** @var DatePeriod $period */
            $period = $this->getPeriodById($id);

            if ($dateTime >= $period->getStartDate() && $dateTime <= $period->getEndDate()) {
                return $id;
            }
        }

        // We can never reach this stage.
        // However, added for keeping the IDEs happy of non returned value.
        throw new Exception('A period could not be found for the requested date');
    }

    /**
     * {@inheritdoc}
     *
     * @param  DateTime|DateTimeImmutable|string $date
     *
     * @throws Exception
     */
    public function getBusinessWeekIdIdByDate($date): int
    {
        $dateTime = $this->getDateObject($date);

        // Instantly throw exception for a date that's out of range of the current financial year.
        // Do this to avoid the resource intensive loop.
        if ($dateTime < $this->fyStartDate || $dateTime > $this->fyEndDate) {
            throw new Exception('The requested date is out of range of the current financial year');
        }

        for ($id = 1; $id <= $this->fyWeeks; $id++) {
            /** @var DatePeriod $week */
            $week = $this->getBusinessWeekById($id);

            if ($dateTime >= $week->getStartDate() && $dateTime <= $week->getEndDate()) {
                return $id;
            }
        }

        // We can never reach this stage.
        // However, added for keeping the IDEs happy of non returned value.
        throw new Exception('A business week could not be found for the specified date');
    }

    /**
     * {@inheritdoc}
     *
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @throws Exception
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeInterface
    {
        $this->validate();

        $this->validatePeriodId($id);

        // If 1st period, get the start of the financial year, regardless of the type.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            return $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' months'));
        }

        // Otherwise return business type calculation.
        return $this->fyStartDate->add(DateInterval::createFromDateString(($id - 1) * 4 . ' weeks'));
    }

    /**
     * {@inheritdoc}
     *
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @throws Exception
     */
    public function getLastDateOfPeriodById(int $id): DateTimeInterface
    {
        $this->validate();

        $this->validatePeriodId($id);

        // If last period, get the end of the financial year, regardless of the type.
        if ($id === $this->fyPeriods) {
            return $this->fyEndDate;
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            return $this->fyStartDate->add(DateInterval::createFromDateString($id . ' months'))
                                     ->sub(DateInterval::createFromDateString('1 day'));
        }

        // Otherwise calculate for business type.
        return $this->fyStartDate->add(DateInterval::createFromDateString($id * 4 . ' weeks'))
                                 ->sub(DateInterval::createFromDateString('1 day'));
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTimeInterface|DateTimeImmutable
     *
     * @throws Exception
     */
    public function getFirstDateOfBusinessWeekById(int $id): DateTimeInterface
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If 1st week, get the start of the financial year.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        return $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' weeks'));
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTimeInterface|DateTimeImmutable
     *
     * @throws Exception
     */
    public function getLastDateOfBusinessWeekById(int $id): DateTimeInterface
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If last week, get the end of the financial year.
        if ($id === $this->fyWeeks) {
            return $this->fyEndDate;
        }

        return $this->fyStartDate->add(DateInterval::createFromDateString($id . ' weeks'))
                                 ->sub(DateInterval::createFromDateString('1 day'));
    }

    /**
     * {@inheritdoc}
     *
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws Exception
     */
    public function getFirstBusinessWeekByPeriodId(int $id): Traversable
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 1);
    }

    /**
     * {@inheritdoc}
     *
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws Exception
     */
    public function getSecondBusinessWeekByPeriodId(int $id): Traversable
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 2);
    }

    /**
     * {@inheritdoc}
     *
     * @return DatePeriod
     *
     * @throws Exception
     */
    public function getThirdBusinessWeekOfPeriodId(int $id): Traversable
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 3);
    }

    /**
     * {@inheritdoc}
     *
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws Exception
     */
    public function getFourthBusinessWeekByPeriodId(int $id): Traversable
    {
        return $this->getBusinessWeekById($id * 4);
    }

    /**
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws  Exception
     * @throws  ConfigException
     */
    public function getFifthThirdBusinessWeekPeriod(): Traversable
    {
        return $this->getBusinessWeekById(53);
    }

    /**
     * First check if calendar type and return value.
     * If not calendar type, it is business type (as the only other option available supported and always set).
     * So we can safely return the relevant value.
     *
     * {@inheritdoc}
     *
     * @return DateTimeImmutable|DateTimeImmutable
     */
    public function getNextFyStartDate(): DateTimeInterface
    {
        // For calendar type, the next year's start date is + 1 year.
        if ($this->isCalendarType($this->type)) {
            return $this->fyStartDate->add(DateInterval::createFromDateString('1 year'));
        }

        // For business type, the next year's start date is + number of weeks.
        // As a financial year would have 52 or 53 weeks, the param handles it.
        return $this->fyStartDate->add(DateInterval::createFromDateString($this->fyWeeks . ' weeks'));
    }

    /**
     * Set the financial year end date.
     *
     * First check for calendar type and return.
     * If not calendar, it can only be business type.
     *
     * @return void
     */
    protected function setFyEndDate(): void
    {
        // We will set end date from the start date object which should be present.
        // Both types calculate end date relative to next financial year start date.
        $nextFinancialYearStartDate = $this->getNextFyStartDate();

        // For calendar type, the end date is 1 year, minus 1 day after the start date.
        if ($this->isCalendarType($this->type)) {
            $this->fyEndDate = $nextFinancialYearStartDate->sub(DateInterval::createFromDateString('1 day'));

            return;
        }

        // For business type, the end date is the number of weeks , minus 1 day after the start date.
        // As a financial year would have 52 or 53 weeks, the param handles it.
        $this->fyEndDate = $nextFinancialYearStartDate->sub(DateInterval::createFromDateString('1 day'));
    }

    /**
     * Get a date object from the provided param.
     *
     * @param  DateTime|DateTimeImmutable|string $date$date
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    protected function getDateObject($date): DateTimeImmutable
    {
        // Placeholder
        $dateTime = null;

        // First check if we have received the an object relevant to the adapter.
        if (is_object($date)) {
            $className = get_class($date);

            if ($className === 'DateTime') {
                return DateTimeImmutable::createFromMutable($date)->setTime(0,0);
            }

            if ($className === 'DateTimeImmutable') {
                return $date->setTime(0,0);
            }
        }

        // Then if a string was passed as param.
        if (is_string($date)) {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $date)->setTime(0,0);
        }

        // Validation that the datetime object was created.
        if (!$dateTime || $dateTime === null) {
            throw new Exception('Invalid date format. Needs to be ISO-8601 string or DateTime/DateTimeImmutable object');
        }

        // Set date object to start of the day.
        return $dateTime;
    }
}