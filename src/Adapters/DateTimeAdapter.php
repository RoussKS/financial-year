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
     * @return Traversable|DatePeriod|DateTimeInterface[]
     *
     * @throws Exception
     * @throws \Exception
     */
    public function getPeriodById(int $id): Traversable
    {
        $this->validate();

        $this->validatePeriodId($id);

        $period = null;

        // Set default values for more readable logic within conditions (for first and last periods).
        // Financial Year start date is the first period's start date.
        // Financial Year end date is the last period's end date.
        $periodStartDate = $this->fyStartDate;
        $periodEndDate = $this->fyEndDate;

        // In calendar type, periods are always 12 as the months, regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            // If first period, period start date is the financial year start date.
            // If not the first period, calculate the correct date.
            if ($id !== 1) {
                $periodStartDate = $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' months'));
            }

            // If last period, period last date is is the financial year end date.
            // If not last period, it's the end of the month.
            if ($id !== 12) {
                $periodEndDate = $periodStartDate->add(DateInterval::createFromDateString('1 month'))
                                                 ->sub(DateInterval::createFromDateString('1 day'));
            }

            $period = new DatePeriod($periodStartDate, DateInterval::createFromDateString('1 day'), $periodEndDate);
        }

        if ($this->isBusinessType($this->type)) {
            // If first period, period start date is the financial year start date.
            // If not the first period, calculate the correct date
            if ($id !== 1) {
                $periodStartDate = $this->fyStartDate->add(DateInterval::createFromDateString(($id - 1) * 4 . ' weeks'));
            }

            // If last period, period last date is is the financial year end date.
            // This way we also overcome the potential issue of a 53rd week.
            // If not last period, it's the end of the month.
            if ($id !== 12) {
                $periodEndDate = $periodStartDate->add(DateInterval::createFromDateString('4 weeks'))
                                                 ->sub(DateInterval::createFromDateString('1 day'));
            }

            $period = new DatePeriod($periodStartDate, DateInterval::createFromDateString('1 day'), $periodEndDate);
        }

        // This case should never happen.
        if ($period === null) {
            throw new Exception('A date range period could not be set');
        }

        return $period;
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

        // If 1st week, get the start of the financial year.
        $weekStartDate = $id === 1 ?
            $this->fyStartDate :
            $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' weeks'));

        // If last week, get the end of the financial year.
        $weekEndDate = $id === $this->fyWeeks ?
            $this->fyEndDate :
            $weekStartDate->add(DateInterval::createFromDateString('6 days'));

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

        for ($id = 1; $id <= 12; $id++) {
            /** @var DatePeriod $interval */
            foreach ($this->getPeriodById($id) as $interval) {
                if ($dateTime >= $interval->getStartDate() && $dateTime <= $interval->getEndDate()) {
                    return $id;
                }
            }
        }

        throw new Exception('A period could not be found for the specified date');
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

        for ($id = 1; $id <= $this->fyWeeks; $id++) {
            foreach ($this->getBusinessWeekById($id) as $interval) {
                if ($dateTime->format('Y-m-d') === $interval->format('Y-m-d')) {
                    return $id;
                }
            }
        }

        throw new Exception('A business week could not be found for the specified date');
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeInterface
    {
        $this->validate();

        $this->validatePeriodId($id);

        $periodStart = null;

        // If 1st period, get the start of the financial year, regardless of the type.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        // In calendar type, periods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            $periodStart = $this->fyStartDate->add(DateInterval::createFromDateString($id - 1 . ' months'));
        }

        if ($this->isBusinessType($this->type)) {
            $periodStart = $this->fyStartDate->add(DateInterval::createFromDateString(($id - 1) * 4 . ' weeks'));
        }

        if ($periodStart === null) {
            throw new Exception('Could not calculate period start date');
        }

        return $periodStart;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getLastDateOfPeriodById(int $id): DateTimeInterface
    {
        $this->validate();

        $this->validatePeriodId($id);

        $periodEnd = null;

        // If last period, get the end of the financial year, regardless of the type.
        if ($id === 12) {
            return $this->fyEndDate;
        }

        // In calendar type, periods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            $periodEnd = $this->fyStartDate->add(DateInterval::createFromDateString($id . ' months'))
                                           ->sub(DateInterval::createFromDateString('1 day'));
        }

        if ($this->isBusinessType($this->type)) {
            $periodEnd = $this->fyStartDate->add(DateInterval::createFromDateString($id * 4 . ' weeks'))
                                           ->sub(DateInterval::createFromDateString('1 day'));
        }

        if ($periodEnd === null) {
            throw new Exception('Could not calculate period end date');
        }

        return $periodEnd;
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
     * Set the financial year end date.
     *
     * @return void
     */
    protected function setFyEndDate(): void
    {
        // We will set end date from the start date object which should be present.

        // For calendar type, the end date is 1 year, minus 1 day after the start date.
        if ($this->isCalendarType($this->type)) {
            $this->fyEndDate = $this->fyStartDate->add(DateInterval::createFromDateString('1 year'))
                                                 ->sub(DateInterval::createFromDateString('1 day'));

        }

        // For business type, the end date is the number of weeks , minus 1 day after the start date.
        if ($this->isBusinessType($this->type)) {
            // As a financial year would have 52 or 53 weeks, the param handles it.
            $this->fyEndDate = $this->fyStartDate->add(DateInterval::createFromDateString($this->fyWeeks . ' weeks'))
                                                 ->sub(DateInterval::createFromDateString('1 day'));
        }
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
        $className = null;

        if (is_object($date)) {
            $className = get_class($date);
        }

        if ($className === 'DateTime') {
            $dateTime = DateTimeImmutable::createFromMutable($date)->setTime(0,0);
        }

        if ($className === 'DateTimeImmutable') {
            $dateTime = $date->setTime(0,0);
        }

        if (is_string($date)) {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $date)->setTime(0,0);
        }

        if (!$dateTime || $dateTime === null) {
            throw new Exception('Invalid date format. Needs to be ISO-8601 string or DateTime/DateTimeImmutable object');
        }

        // Set date object to start of the day.
        return $dateTime;
    }
}