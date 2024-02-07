<?php

namespace RoussKS\FinancialYear;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use Traversable;

/**
 * Implementation of PHP DateTime FinancialYear Adapter
 *
 * Class DateTimeAdapter
 *
 * @package RoussKS\FinancialYear
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
     * @var DateTimeZone|null
     */
    private $dateTimeZone;

    /**
     * DateTimeAdapter constructor.
     *
     * @param string $fyType
     * @param DateTime|DateTimeImmutable|DateTimeInterface|string $fyStartDate // string must be of ISO-8601 format 'YYYY-MM-DD'
     * @param bool $fiftyThreeWeeks
     * @param DateTimeZone|string|null $dateTimeZone // this will be used only and only if a string was provided for start date
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function __construct(string $fyType, $fyStartDate, bool $fiftyThreeWeeks = false, $dateTimeZone = null)
    {
        parent::__construct($fyType, $fiftyThreeWeeks);

        // First set the timezone if start date is a string,
        // then the start date and then auto calculate the end date of the financial year.
        $this->setDateTimeZone(is_string($fyStartDate) ? $dateTimeZone : null);
        $this->setFyStartDate($fyStartDate);

        $this->autoSetFyEndDateByStartDate();
    }

    /**
     * {@inheritdoc}
     *
     * Extend parent class in order to recalculate end date if the business year weeks change.
     *
     * @throws Exception
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void
    {
        $originalFyWeeks = $this->fyWeeks;

        parent::setFyWeeks($fiftyThreeWeeks);

        // Reset the financial year's end date according to the weeks setting.
        if ($originalFyWeeks !== null && $originalFyWeeks !== $this->fyWeeks) {
            $this->autoSetFyEndDateByStartDate();
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

        $this->validateStartDate();

        // If this method execution is not triggered on instantiation (constructor) which performs the same action,
        // recalculate financial year's end date from current settings,
        // even if the new start date is the same as the previous one (why re-setting the same date?).
        if ($originalFyStartDate !== null) {
            $this->autoSetFyEndDateByStartDate();
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
     * Otherwise, it is business type as the only other available.
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
        return new DatePeriod(
            $this->getFirstDateOfBusinessWeekById($id),
            DateInterval::createFromDateString('1 day'),
            $this->getLastDateOfBusinessWeekById($id)
        );
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

        $this->validateDateBelongsToCurrentFinancialYear($dateTime);

        for ($id = 1; $id <= $this->fyPeriods; $id++) {
            // If the date is between the start and the end date of the period, get the period's id.
            if ($dateTime >= $this->getFirstDateOfPeriodById($id) && $dateTime <= $this->getLastDateOfPeriodById($id)) {
                return $id;
            }
        }

        // We can never reach this stage.
        // However, added for keeping the IDEs happy of non returned value.
        throw new Exception('A period could not be found for the requested date.');
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

        if (!$this->isBusinessType($this->getType())) {
            throw new ConfigException('Business weeks are set only for a business type financial year.');
        }

        $this->validateDateBelongsToCurrentFinancialYear($dateTime);

        for ($id = 1; $id <= $this->fyWeeks; $id++) {
            if (
                $dateTime >= $this->getFirstDateOfBusinessWeekById($id) &&
                $dateTime <= $this->getLastDateOfBusinessWeekById($id)
            ) {
                return $id;
            }
        }

        // We can never reach this stage.
        // However, added for keeping the IDEs happy of non returned value.
        throw new Exception('A business week could not be found for the specified date.');
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
            return $this->getFyStartDate();
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->getType())) {
            return $this->getFyStartDate()->modify('+' . ($id - 1) . ' months');
        }

        // Otherwise return business type calculation.
        return $this->getFyStartDate()->modify('+' . ($id - 1) * 4 . ' weeks');
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
            return $this->getFyEndDate();
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType($this->getType())) {
            // Otherwise calculate for business type.
            return $this->getFyStartDate()->modify('+' . $id . ' months')->modify('-1 day');
        }

        // Otherwise calculate for business type.
        return $this->getFyStartDate()->modify('+' . $id * 4 . ' weeks')->modify('-1 day');
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
            return $this->getFyStartDate();
        }

        return $this->getFyStartDate()->modify('+' . ($id - 1) . ' weeks');
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
            return $this->getFyEndDate();
        }

        return $this->getFyStartDate()->modify('+' . $id . ' weeks')->modify('-1 day');
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
     * {@inheritdoc}
     *
     * @return DatePeriod|DateTimeImmutable[]
     *
     * @throws Exception
     */
    public function getFiftyThirdBusinessWeek(): Traversable
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
        if ($this->isCalendarType($this->getType())) {
            return $this->getFyStartDate()->modify('+1 year');
        }

        // For business type, the next year's start date is + number of weeks.
        // As a financial year would have 52 or 53 weeks, the param handles it.
        return $this->getFyStartDate()->modify('+' . $this->fyWeeks . ' weeks');
    }

    /**
     * Validate that the start date is not disallowed.
     *
     * @return void
     *
     * @throws ConfigException
     */
    protected function validateStartDate(): void
    {
        $disallowedFyCalendarTypeDates = ['29', '30', '31'];

        if (
            $this->isCalendarType($this->getType()) &&
            in_array($this->getFyStartDate()->format('d'), $disallowedFyCalendarTypeDates, true)
        ) {
            $this->throwConfigurationException(
                'This library does not support 29, 30, 31 as start dates of a month for calendar type financial year.'
            );
        }
    }

    /**
     * Validate that a date belongs to the set financial year.
     *
     * @param  DateTimeImmutable $dateTime
     *
     * @return void
     *
     * @throws Exception
     */
    protected function validateDateBelongsToCurrentFinancialYear(DateTimeImmutable $dateTime): void
    {
        if ($dateTime < $this->getFyStartDate() || $dateTime > $this->getFyEndDate()) {
            throw new Exception('The requested date is out of range of the current financial year.');
        }
    }

    /**
     * Automatically set the financial year's end date by the current start date.
     *
     * We will set end date from the start date object which should be present.
     * Both types calculate end date relative to next financial year start date.
     * As that is automatically calculated for us, regardless of type, we just subtract 1 day.
     *
     * @return void
     */
    protected function autoSetFyEndDateByStartDate(): void
    {
        $this->fyEndDate = $this->getNextFyStartDate()->modify('-1 day');
    }

    /**
     * Get the DateTimeZone currently set
     *
     * @return DateTimeZone|null
     */
    protected function getDateTimeZone(): ?DateTimeZone
    {
        return $this->dateTimeZone;
    }

    /**
     * @param DateTimeZone|string|null $dateTimeZone
     * @return void
     * @throws ConfigException
     */
    protected function setDateTimeZone($dateTimeZone = null): void
    {
        if ($dateTimeZone === null) {
            return;
        }

        if ($dateTimeZone instanceof DateTimeZone) {
            $this->dateTimeZone = $dateTimeZone;
            return;
        }

        if (is_string($dateTimeZone)) {
            try {
                $this->dateTimeZone = new DateTimeZone($dateTimeZone);
                return;
            } catch (\Exception $ex) {
                // Catch exception, set null timezone string and throw config exception.
                $this->throwConfigurationException('Invalid dateTimeZone string: ' . $dateTimeZone);
            }
        }

        $this->throwConfigurationException('Invalid dateTimeZone parameter');
    }

    /**
     * Get & validate a DateTimeImmutable object for the given parameter.
     * If the object is generated, we set it to the start of the day (0, 0) with setTime.
     * setTime will not return false for valid input of hours and minutes.
     *
     * @param  DateTime|DateTimeImmutable|string $date
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    protected function getDateObject($date): DateTimeImmutable
    {
        $dateTime = $this->generateDateTimeImmutableObject($date);

        // Validation that the datetime object was created and set to the start of the day.
        if (!$dateTime) {
            throw new Exception(
                'Invalid date format. Not a valid ISO-8601 date string or DateTime/DateTimeImmutable object.'
            );
        }

        /** @var DateTimeImmutable $dateTime */
        $dateTime = $dateTime->setTime(0, 0);

        // We have set a valid hour and minutes, so false is not a possible result of the above method.
        return $dateTime;
    }

    /**
     * Generate and return a DateTimeImmutable object for the given $date parameter.
     *
     * First check if we have received an object relevant to the adapter and return it.
     * This can be either a DateTime or DateTimeImmutable object.
     *
     * Otherwise, create the object regardless of the type with createFromFormat.
     * It will return false if it fails.
     *
     * @param  DateTime|DateTimeImmutable|string $date
     *
     * @return DateTimeImmutable|false
     */
    protected function generateDateTimeImmutableObject($date)
    {
        if ($date instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($date);
        }

        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        return DateTimeImmutable::createFromFormat('Y-m-d', $date, $this->getDateTimeZone());
    }
}
