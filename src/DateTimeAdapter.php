<?php

namespace RoussKS\FinancialYear;

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
     * @var DateTimeImmutable
     */
    protected $fyStartDate;

    /**
     * @var DateTimeImmutable
     */
    protected $fyEndDate;

    /**
     * DateTimeAdapter constructor.
     *
     * @param  string $fyType
     * @param  DateTime|DateTimeImmutable|string $fyStartDate, string must be of ISO-8601 format 'YYYY-MM-DD'
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function __construct(string $fyType, $fyStartDate, bool $fiftyThreeWeeks = false)
    {
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
     * @return DateTimeImmutable
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

        $disallowedFyCalendarTypeDates = ['29', '30', '31'];

        if (
            $this->isCalendarType($this->type) &&
            in_array($this->fyStartDate->format('d'), $disallowedFyCalendarTypeDates, true)
        ) {
            $this->throwConfigurationException(
                'This library does not support start dates for 29, 30, 31 of each month for calendar type financial year'
            );
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
     * @return DateTimeImmutable
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
     * @return DatePeriod|DateTimeImmutable[]
     *
     * @throws Exception
     * @throws \Exception
     */
    public function getPeriodById(int $id): Traversable
    {
        return new DatePeriod(
            $this->getFirstDateOfPeriodById($id),
            DateInterval::createFromDateString('1 day'),
            $this->getLastDateOfPeriodById($id)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return DatePeriod|DateTimeImmutable[]
     *
     * @throws Exception
     */
    public function getBusinessWeekById(int $id): Traversable
    {
        $this->validateConfiguration();

        $this->validateBusinessWeekId($id);

        // Set default values.
        // Will be used if first or last week.
        $weekStartDate = $this->fyStartDate;
        $weekEndDate = $this->fyEndDate;

        // If not the first week, calculate period start date.
        if ($id !== 1) {
            $weekStartDate = $this->fyStartDate->modify('+' . $id - 1 . ' weeks');
        }

        // If not last week of the year, calculate period end date from start date.
        if ($id !== $this->fyWeeks) {
            $weekEndDate = $weekStartDate->modify('+ 6 days');
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
            // If the date is between the start and the end date of the period, get the period's id.
            if ($dateTime >= $this->getFirstDateOfPeriodById($id) && $dateTime <= $this->getLastDateOfPeriodById($id)) {
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
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeInterface
    {
        $this->validateConfiguration();

        $this->validatePeriodId($id);

        // If 1st period, get the start of the financial year, regardless of the type.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            return $this->fyStartDate->modify('+' . $id - 1 . ' months');
        }

        // Otherwise return business type calculation.
        return $this->fyStartDate->modify('+' . ($id - 1) * 4 . ' weeks');
    }

    /**
     * {@inheritdoc}
     *
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    public function getLastDateOfPeriodById(int $id): DateTimeInterface
    {
        $this->validateConfiguration();

        $this->validatePeriodId($id);

        // If last period, get the end of the financial year, regardless of the type.
        if ($id === $this->fyPeriods) {
            return $this->fyEndDate;
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->type)) {
            // Otherwise calculate for business type.
            return $this->fyStartDate->modify('+' . $id . ' months')
                                     ->modify('-1 day');
        }

        // Otherwise calculate for business type.
        return $this->fyStartDate->modify('+' . $id * 4 . ' weeks')
                                 ->modify('-1 day');
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    public function getFirstDateOfBusinessWeekById(int $id): DateTimeInterface
    {
        $this->validateConfiguration();

        $this->validateBusinessWeekId($id);

        // If 1st week, get the start of the financial year.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        return $this->fyStartDate->modify('+' . $id - 1 . ' weeks');
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    public function getLastDateOfBusinessWeekById(int $id): DateTimeInterface
    {
        $this->validateConfiguration();

        $this->validateBusinessWeekId($id);

        // If last week, get the end of the financial year.
        if ($id === $this->fyWeeks) {
            return $this->fyEndDate;
        }

        return $this->fyStartDate->modify('+' . $id . ' weeks')
                                 ->modify('-1 day');
    }

    /**
     * {@inheritdoc}
     *
     * @return DatePeriod|DateTimeImmutable[]
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
     * @return DatePeriod|DateTimeImmutable[]
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
     * @return DatePeriod|DateTimeImmutable[]
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
     * @return DatePeriod|DateTimeImmutable[]
     *
     * @throws Exception
     */
    public function getFourthBusinessWeekByPeriodId(int $id): Traversable
    {
        return $this->getBusinessWeekById($id * 4);
    }

    /**
     * @return DatePeriod|DateTimeImmutable[]
     *
     * @throws  Exception
     * @throws  ConfigException
     */
    public function getFiftyThirdWeek(): Traversable
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
     * @return DateTimeImmutable
     */
    public function getNextFyStartDate(): DateTimeInterface
    {
        // For calendar type, the next year's start date is + 1 year.
        if ($this->isCalendarType($this->type)) {
            return $this->fyStartDate->modify('+1 year');
        }

        // For business type, the next year's start date is + number of weeks.
        // As a financial year would have 52 or 53 weeks, the param handles it.
        return $this->fyStartDate->modify('+' . $this->fyWeeks . ' weeks');
    }

    /**
     * Set the financial year end date.
     *
     * We will set end date from the start date object which should be present.
     * Both types calculate end date relative to next financial year start date.
     * As that is automatically calculated for us, regardless of type, we just subtract 1 day.
     *
     * @return void
     */
    protected function setFyEndDate(): void
    {
        $this->fyEndDate = $this->getNextFyStartDate()->modify('-1 day');
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
        // Get classname of date param if object, else false if not an object
        if (is_object($date)) {
            $className = get_class($date);
        }

        // First check if we have received the object relevant to the adapter.
        // If we did, return the required DateTimeImmutable.
        if (isset($className) && $className === 'DateTime') {
            return DateTimeImmutable::createFromMutable($date)->setTime(0, 0);
        }

        if (isset($className) && $className === 'DateTimeImmutable') {
            return $date->setTime(0, 0);
        }

        // Then if a string was passed as param, create the DateTimeImmutable.
        if (is_string($date)) {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        }

        // Validation that the datetime object was created.
        if (!isset($dateTime) || !$dateTime) {
            throw new Exception('Invalid date format. Needs to be ISO-8601 string or DateTime/DateTimeImmutable object');
        }

        // Set date object to start of the day and return.
        return $dateTime->setTime(0, 0);
    }
}