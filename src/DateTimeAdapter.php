<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;

/**
 * Implementation of PHP DateTime FinancialYear Adapter
 */
class DateTimeAdapter extends AbstractAdapter implements AdapterInterface
{
    private DateTimeZone $dateTimeZone;

    /**
     * @param DateTimeInterface|string $fyStartDate  // string must be of ISO-8601 format 'YYYY-MM-DD'
     * @param DateTimeZone|string|null $dateTimeZone  // this will be used only and only if a string was provided for start date
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function __construct(
        string $fyType,
        DateTimeInterface|string $fyStartDate,
        bool $fiftyThreeWeeks = false,
        DateTimeZone|string|null $dateTimeZone = null
    ) {
        parent::__construct($fyType, $fiftyThreeWeeks);

        // First set the timezone, use param if start date is a string, otherwise use datetime object timezone.
        // then the start date and then auto calculate the end date of the financial year.
        $this->setDateTimeZone(is_string($fyStartDate) ? $dateTimeZone : $fyStartDate->getTimezone());
        $this->setFyStartDate($fyStartDate);

        $this->autoSetFyEndDateByStartDate();
    }

    /**
     * {@inheritDoc}
     *
     * Extend parent class in order to recalculate end date if the business year weeks change.
     *
     * @throws Exception
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void
    {
        $originalFyWeeks = $this->getFyWeeks();

        parent::setFyWeeks($fiftyThreeWeeks);

        // Reset the financial year's end date according to the weeks setting.
        if ($originalFyWeeks !== null && $originalFyWeeks !== $this->getFyWeeks()) {
            $this->autoSetFyEndDateByStartDate();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFyStartDate(): DateTimeImmutable
    {
        /** @var \DateTimeImmutable */
        return $this->fyStartDate;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function setFyStartDate(DateTimeInterface|string $date): void
    {
        // fyStartDate property is an immutable object.
        $originalFyStartDate = $this->fyStartDate;

        $this->fyStartDate = $this->getDateObject($date);

        $this->validateStartDate();

        // If this method execution is not triggered on instantiation (constructor) which performs the same action,
        // set the timezone again (in case it is changed) & recalculate financial year's end date from current settings,
        // even if the new start date is the same as the previous one (why re-setting the same date?).
        if ($originalFyStartDate !== null) {
            $this->setDateTimeZone($this->getFyStartDate()->getTimezone());
            $this->autoSetFyEndDateByStartDate();
        }
    }

    public function getFyEndDate(): DateTimeImmutable
    {
        /** @var DateTimeImmutable */
        return $this->fyEndDate;
    }

    /**
     * {@inheritDoc}
     *
     * First check for calendar type and the return the corresponding value.
     * Otherwise, it is business type as the only other available.
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     * @throws \Exception
     */
    public function getPeriodById(int $id): DatePeriod
    {
        return new DatePeriod(
            $this->getFirstDateOfPeriodById($id),
            DateInterval::createFromDateString('1 day'),
            $this->getLastDateOfPeriodById($id)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     */
    public function getBusinessWeekById(int $id): DatePeriod
    {
        return new DatePeriod(
            $this->getFirstDateOfBusinessWeekById($id),
            DateInterval::createFromDateString('1 day'),
            $this->getLastDateOfBusinessWeekById($id)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getPeriodIdByDate(DateTimeInterface|string $date): int
    {
        $dateTime = $this->getDateObject($date);

        $this->validateDateBelongsToCurrentFinancialYear($dateTime);

        for ($id = 1; $id <= $this->getFyPeriods(); $id++) {
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
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getBusinessWeekIdIdByDate(DateTimeInterface|string $date): int
    {
        $dateTime = $this->getDateObject($date);

        if (!$this->isBusinessType($this->getType())) {
            throw new ConfigException('Business weeks are set only for a business type financial year.');
        }

        $this->validateDateBelongsToCurrentFinancialYear($dateTime);

        for ($id = 1; $id <= $this->getFyWeeks(); $id++) {
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
     * {@inheritDoc}
     *
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @throws Exception
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeImmutable
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
     * {@inheritDoc}
     *
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @throws Exception
     */
    public function getLastDateOfPeriodById(int $id): DateTimeImmutable
    {
        $this->validateConfiguration();

        $this->validatePeriodId($id);

        // If last period, get the end of the financial year, regardless of the type.
        if ($id === $this->getFyPeriods()) {
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
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getFirstDateOfBusinessWeekById(int $id): DateTimeImmutable
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
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getLastDateOfBusinessWeekById(int $id): DateTimeImmutable
    {
        $this->validateConfiguration();

        $this->validateBusinessWeekId($id);

        // If last week, get the end of the financial year.
        if ($id === $this->getFyWeeks()) {
            return $this->getFyEndDate();
        }

        return $this->getFyStartDate()->modify('+' . $id . ' weeks')->modify('-1 day');
    }

    /**
     * {@inheritDoc}
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     */
    public function getFirstBusinessWeekByPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 1);
    }

    /**
     * {@inheritDoc}
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     */
    public function getSecondBusinessWeekByPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 2);
    }

    /**
     * {@inheritDoc}
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     */
    public function getThirdBusinessWeekOfPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 3);
    }

    /**
     * {@inheritDoc}
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     */
    public function getFourthBusinessWeekByPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById($id * 4);
    }

    /**
     * {@inheritDoc}
     *
     * @return DatePeriod<DateTimeImmutable, DateTimeImmutable, null>
     *
     * @throws Exception
     */
    public function getFiftyThirdBusinessWeek(): DatePeriod
    {
        return $this->getBusinessWeekById(53);
    }

    /**
     * First check if calendar type and return value.
     * If not calendar type, it is business type (as the only other option available supported and always set).
     * So we can safely return the relevant value.
     */
    public function getNextFyStartDate(): DateTimeImmutable
    {
        // For calendar type, the next year's start date is + 1 year.
        if ($this->isCalendarType($this->getType())) {
            return $this->getFyStartDate()->modify('+1 year');
        }

        // For business type, the next year's start date is + number of weeks.
        // As a financial year would have 52 or 53 weeks, the param handles it.
        return $this->getFyStartDate()->modify('+' . $this->getFyWeeks() . ' weeks');
    }

    /**
     * Validate that the start date is not disallowed.
     *
     * @throws ConfigException
     */
    protected function validateStartDate(): void
    {
        $disallowedFyCalendarTypeDates = ['29', '30', '31'];

        if (
            $this->isCalendarType($this->getType())
            && in_array($this->getFyStartDate()->format('d'), $disallowedFyCalendarTypeDates, true)
        ) {
            $this->throwConfigurationException(
                'This library does not support 29, 30, 31 as start dates of a month for calendar type financial year.'
            );
        }
    }

    /**
     * Validate that a date belongs to the set financial year.
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
     */
    protected function autoSetFyEndDateByStartDate(): void
    {
        $this->fyEndDate = $this->getNextFyStartDate()->modify('-1 day');
    }

    /**
     * Get the DateTimeZone currently set
     */
    private function getDateTimeZone(): DateTimeZone
    {
        return $this->dateTimeZone;
    }

    /**
     * @throws ConfigException
     */
    private function setDateTimeZone(DateTimeZone|string|null $dateTimeZone = null): void
    {
        // If a DateTimeZone object is provided, set it directly.
        if ($dateTimeZone instanceof DateTimeZone) {
            $this->dateTimeZone = $dateTimeZone;
            return;
        }

        // Otherwise, set the DateTimeZone either by the string provided or the system's default if null provided.
        // If none of the other options, a string is provided
        try {
            $this->dateTimeZone = new DateTimeZone($dateTimeZone ?? date_default_timezone_get());
        } catch (\Exception) {
            // Catch exception and throw as config exception.
            $this->throwConfigurationException('Invalid dateTimeZone string: ' . $dateTimeZone);
        }
    }

    /**
     * Get & validate a DateTimeImmutable object for the given parameter.
     * If the object is generated, we set it to the start of the day (0, 0) with setTime.
     * setTime will not return false for valid input of hours and minutes.
     *
     * @throws Exception
     */
    protected function getDateObject(DateTimeInterface|string $date): DateTimeImmutable
    {
        return $this->generateImmutableObjectFromDatetime($date)->setTime(0, 0);
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
     * @throws Exception
     */
    private function generateImmutableObjectFromDatetime(DateTimeInterface|string $date): DateTimeImmutable
    {
        if ($date instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($date);
        }

        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        $dateImmutable = DateTimeImmutable::createFromFormat('Y-m-d', $date, $this->getDateTimeZone());

        if ($dateImmutable === false) {
            throw new Exception('Provided date `' . $date . '` is not a valid ISO-8601 date string.');
        }

        return $dateImmutable;
    }
}
