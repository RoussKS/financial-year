<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception as FinancialYearException;
use Traversable;

/**
 * Implementation of PHP DateTime FinancialYear Adapter
 */
final class DateTimeAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * @var DateTimeImmutable
     */
    protected DateTimeInterface $fyStartDate;

    /**
     * @var DateTimeImmutable
     */
    protected DateTimeInterface $fyEndDate;
    private DateTimeZone|null $dateTimeZone = null;

    /**
     * @param DateTimeInterface|string $fyStartDate // string must be of ISO-8601 format 'YYYY-MM-DD'
     * @param DateTimeZone|string|null $dateTimeZone // this will be used only and only if a string was provided for start date
     *
     * @return void
     *
     * @throws ConfigException
     * @throws FinancialYearException
     */
    public function __construct(
        string $fyType,
        DateTimeInterface|string $fyStartDate,
        bool $fiftyThreeWeeks = false,
        DateTimeZone|string|null $dateTimeZone = null
    ) {
        parent::__construct(type: $fyType, fiftyThreeWeeks: $fiftyThreeWeeks);

        // First set the timezone if start date is a string,
        // then the start date which auto calculates the end date of the financial year.
        $this->setDateTimeZone(dateTimeZone: is_string(value: $fyStartDate) ? $dateTimeZone : null);
        $this->setFyStartDate(date: $fyStartDate);
    }

    /**
     * Extend parent class in order to recalculate end date if the business year weeks change.
     *
     * @throws FinancialYearException
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void
    {
        $originalFyWeeks = $this->fyWeeks;

        parent::setFyWeeks(fiftyThreeWeeks: $fiftyThreeWeeks);

        // Reset the financial year's end date according to the weeks setting.
        if ($originalFyWeeks !== null && $originalFyWeeks !== $this->getFyWeeks()) {
            $this->autoSetFyEndDateByStartDate();
        }
    }

    public function getFyStartDate(): DateTimeImmutable
    {
        return $this->fyStartDate;
    }

    /**
     * @throws FinancialYearException
     */
    public function setFyStartDate(string|DateTimeInterface $date): void
    {
        $this->fyStartDate = $this->getDateObject(date: $date);

        $this->validateStartDate();

        // Recalculate financial year's end date from current settings,
        $this->autoSetFyEndDateByStartDate();
    }

    public function getFyEndDate(): DateTimeImmutable
    {
        return $this->fyEndDate;
    }

    /**
     * First check for calendar type and the return the corresponding value.
     * Otherwise, it is business type as the only other available.
     *
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     * @throws Exception
     */
    public function getPeriodById(int $id): DatePeriod
    {
        return new DatePeriod(
            $this->getFirstDateOfPeriodById(id: $id),
            DateInterval::createFromDateString(datetime: '1 day'),
            $this->getLastDateOfPeriodById(id: $id)
        );
    }

    /**
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     * @throws Exception
     */
    public function getBusinessWeekById(int $id): DatePeriod
    {
        return new DatePeriod(
            $this->getFirstDateOfBusinessWeekById(id: $id),
            DateInterval::createFromDateString(datetime: '1 day'),
            $this->getLastDateOfBusinessWeekById(id: $id)
        );
    }

    /**
     * @throws FinancialYearException
     */
    public function getPeriodIdByDate(DateTimeInterface|string $date): int
    {
        $dateTime = $this->getDateObject(date: $date);

        $this->validateDateBelongsToCurrentFinancialYear(dateTime: $dateTime);

        for ($id = 1; $id <= $this->getFyPeriods(); $id++) {
            // If the date is between the start and the end date of the period, get the period's id.
            if ($dateTime >= $this->getFirstDateOfPeriodById(id: $id) &&
                $dateTime <= $this->getLastDateOfPeriodById(id: $id)
            ) {
                return $id;
            }
        }

        // We can never reach this stage.
        // However, added for keeping the IDEs happy of non returned value.
        throw new FinancialYearException(message: 'A period could not be found for the requested date.');
    }

    /**
     * @throws FinancialYearException
     */
    public function getBusinessWeekIdIdByDate(DateTimeInterface|string $date): int
    {
        $dateTime = $this->getDateObject(date: $date);

        if (!$this->isBusinessType(value: $this->getType())) {
            throw new ConfigException(message: 'Business weeks are set only for a business type financial year.');
        }

        $this->validateDateBelongsToCurrentFinancialYear(dateTime: $dateTime);

        for ($id = 1; $id <= $this->fyWeeks; $id++) {
            if (
                $dateTime >= $this->getFirstDateOfBusinessWeekById(id: $id) &&
                $dateTime <= $this->getLastDateOfBusinessWeekById(id: $id)
            ) {
                return $id;
            }
        }

        // We can never reach this stage.
        // However, added for keeping the IDEs happy of non returned value.
        throw new FinancialYearException(message: 'A business week could not be found for the specified date.');
    }

    /**
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @throws FinancialYearException
     */
    public function getFirstDateOfPeriodById(int $id): DateTimeImmutable
    {
        $this->validatePeriodId(id: $id);

        // If 1st period, get the start of the financial year, regardless of the type.
        if ($id === 1) {
            return $this->getFyStartDate();
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType(value: $this->getType())) {
            return $this->getFyStartDate()->modify(modifier: '+' . ($id - 1) . ' months');
        }

        // Otherwise return business type calculation.
        return $this->getFyStartDate()->modify(modifier: '+' . ($id - 1) * 4 . ' weeks');
    }

    /**
     * First check for calendar type.
     * Otherwise, it will be business type as no other is supported.
     *
     * @throws FinancialYearException
     */
    public function getLastDateOfPeriodById(int $id): DateTimeImmutable
    {
        $this->validatePeriodId(id: $id);

        // If last period, get the end of the financial year, regardless of the type.
        if ($id === $this->fyPeriods) {
            return $this->getFyEndDate();
        }

        // In calendar type, fyPeriods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->isCalendarType(value: $this->getType())) {
            // Otherwise calculate for business type.
            return $this->getFyStartDate()->modify(modifier: '+' . $id . ' months')->modify(modifier: '-1 day');
        }

        // Otherwise calculate for business type.
        return $this->getFyStartDate()->modify(modifier: '+' . $id * 4 . ' weeks')->modify(modifier: '-1 day');
    }

    /**
     * @throws FinancialYearException
     */
    public function getFirstDateOfBusinessWeekById(int $id): DateTimeImmutable
    {
        $this->validateBusinessWeekId(id: $id);

        // If 1st week, get the start of the financial year.
        if ($id === 1) {
            return $this->getFyStartDate();
        }

        return $this->getFyStartDate()->modify(modifier: '+' . ($id - 1) . ' weeks');
    }

    /**
     * @throws FinancialYearException
     */
    public function getLastDateOfBusinessWeekById(int $id): DateTimeImmutable
    {
        $this->validateBusinessWeekId(id: $id);

        // If last week, get the end of the financial year.
        if ($id === $this->fyWeeks) {
            return $this->getFyEndDate();
        }

        return $this->getFyStartDate()->modify(modifier: '+' . $id . ' weeks')->modify(modifier: '-1 day');
    }

    /**
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     */
    public function getFirstBusinessWeekByPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(id: ($id - 1) * 4 + 1);
    }

    /**
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     */
    public function getSecondBusinessWeekByPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(id: ($id - 1) * 4 + 2);
    }

    /**
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     */
    public function getThirdBusinessWeekOfPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(id: ($id - 1) * 4 + 3);
    }

    /**
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     */
    public function getFourthBusinessWeekByPeriodId(int $id): DatePeriod
    {
        return $this->getBusinessWeekById(id: $id * 4);
    }

    /**
     * @return DatePeriod<DateTimeImmutable>
     *
     * @throws FinancialYearException
     */
    public function getFiftyThirdBusinessWeek(): DatePeriod
    {
        return $this->getBusinessWeekById(id: 53);
    }

    /**
     * First check if calendar type and return value.
     * If not calendar type, it is business type (as the only other option available supported and always set).
     * So we can safely return the relevant value.
     */
    public function getNextFyStartDate(): DateTimeImmutable
    {
        // For calendar type, the next year's start date is + 1 year.
        if ($this->isCalendarType(value: $this->getType())) {
            return $this->getFyStartDate()->modify(modifier: '+1 year');
        }

        // For business type, the next year's start date is + number of weeks.
        // As a financial year would have 52 or 53 weeks, the param handles it.
        return $this->getFyStartDate()->modify(modifier: '+' . $this->getFyWeeks() . ' weeks');
    }

    public function validateConfiguration(): void
    {
        // No validation required.
    }

    /**
     * Validate that the start date is not disallowed.
     * @throws ConfigException
     */
    private function validateStartDate(): void
    {
        $disallowedFyCalendarTypeDates = ['29', '30', '31'];

        if (
            $this->isCalendarType(value: $this->getType()) &&
            in_array(
                needle: $this->getFyStartDate()->format(format: 'd'),
                haystack: $disallowedFyCalendarTypeDates,
                strict: true
            )
        ) {
            $this->throwConfigurationException(
                message: 'This library does not support 29, 30, 31 as start dates of a month for calendar type financial year.'
            );
        }
    }

    /**
     * Validate that a date belongs to the set financial year.
     *
     * @throws FinancialYearException
     */
    private function validateDateBelongsToCurrentFinancialYear(DateTimeImmutable $dateTime): void
    {
        if ($dateTime < $this->getFyStartDate() || $dateTime > $this->getFyEndDate()) {
            throw new FinancialYearException(
                message: 'The requested date is out of range of the current financial year.'
            );
        }
    }

    /**
     * Automatically set the financial year's end date by the current start date.
     *
     * We will set end date from the start date object which should be present.
     * Both types calculate end date relative to next financial year start date.
     * As that is automatically calculated for us, regardless of type, we just subtract 1 day.
     */
    private function autoSetFyEndDateByStartDate(): void
    {
        $this->fyEndDate = $this->getNextFyStartDate()->modify(modifier: '-1 day');
    }

    /**
     * Get the DateTimeZone currently set
     */
    protected function getDateTimeZone(): ?DateTimeZone
    {
        return $this->dateTimeZone;
    }

    /**
     * @throws ConfigException
     */
    private function setDateTimeZone(DateTimeZone|string|null $dateTimeZone = null): void
    {
        if ($dateTimeZone === null) {
            return;
        }

        if (is_string($dateTimeZone)) {
            try {
                $this->dateTimeZone = new DateTimeZone($dateTimeZone);
                return;
            } catch (Exception) {
                // Catch exception, set null timezone string and throw config exception.
                $this->throwConfigurationException(message: 'Invalid dateTimeZone string: ' . $dateTimeZone);
            }
        }

        // Otherwise it's a DateTimeZone object, so we can set it directly.
        $this->dateTimeZone = $dateTimeZone;

    }

    /**
     * Get & validate a DateTimeImmutable object for the given parameter.
     * If the object is generated, we set it to the start of the day (0, 0) with setTime.
     * setTime will not return false for valid input of hours and minutes.
     *
     * @throws FinancialYearException
     */
    private function getDateObject(DateTimeInterface|string $date): DateTimeImmutable
    {
        $dateTime = $this->generateDateTimeImmutableObject(date: $date);

        // Validation that the datetime object was created and set to the start of the day.
        if (!$dateTime) {
            throw new FinancialYearException(
                message: 'Invalid date format. Not a valid ISO-8601 date string or DateTime/DateTimeImmutable object.'
            );
        }

        // Return new immutable object with the time set to 0, 0 (start of the day).
        return $dateTime->setTime(hour: 0,minute: 0);
    }

    /**
     * Generate and return a DateTimeImmutable object for the given $date parameter.
     *
     * First check if we have received a DateTimeImmutable object and return it.
     * If we have any other DateTimeInterface object, create DateTimeImmutable from it.
     *
     * Otherwise, create the object from string with createFromFormat.
     * It will return false if it fails.
     */
    private function generateDateTimeImmutableObject(DateTimeInterface|string $date): DateTimeImmutable|false
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface(object: $date);
        }

        return DateTimeImmutable::createFromFormat(
            format: 'Y-m-d',
            datetime: $date,
            timezone: $this->getDateTimeZone()
        );
    }
}
