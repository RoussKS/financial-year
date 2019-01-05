<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\FinancialYear\Enums\TypeEnum;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Interfaces\AdapterInterface;

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
     * @var \DateTimeImmutable
     */
    protected $fyStartDate;

    /**
     * @var \DateTimeImmutable
     */
    protected $fyEndDate;

    /**
     * DateTimeAdapter constructor.
     *
     * $fyEndDate, if provided, has priority and overrides $fiftyThreeWeeks for 'business' $fyType.
     *
     * @param  string $fyType
     * @param  \DateTime|\DateTimeImmutable|string $fyStartDate
     * @param  bool $fiftyThreeWeeks
     * @param  \DateTime|\DateTimeImmutable|string|null $fyEndDate
     *
     * @return void
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function __construct(
        string $fyType,
        $fyStartDate,
        bool $fiftyThreeWeeks = false,
        $fyEndDate = null
    ) {
        parent::__construct($fyType, $fiftyThreeWeeks);

        $this->setFyStartDate($fyStartDate);

        $this->setFyEndDate($fyEndDate);
    }

    /**
     * {@inheritdoc}
     *
     * Extend parent class in order to recalculate end date if the business year weeks change.
     */
    public function setFyWeeks($fiftyThreeWeeks = false)
    {
        $originalFyWeeks = $this->fyWeeks;

        parent::setFyWeeks($fiftyThreeWeeks);

        // Reset the financial year end date according to the weeks setting.
        if ($originalFyWeeks !== null && $originalFyWeeks !== $this->fyWeeks) {
            $this->setFyEndDate(null);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTimeInterface|\DateTimeImmutable
     */
    public function getFyStartDate()
    {
        return $this->fyStartDate;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime|\DateTimeImmutable|string $date
     */
    public function setFyStartDate($date)
    {
        // fyStartDate property is an immutable object.
        $originalFyStartDate = $this->fyStartDate;

        if ($date instanceof \DateTime) {
            $this->fyStartDate = \DateTimeImmutable::createFromMutable($date);
        }

        if ($date instanceof \DateTimeImmutable) {
            $this->fyStartDate = $date;
        }

        if (is_string($date)) {
            $this->fyStartDate = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        }

        if (!$this->fyStartDate || $this->fyStartDate === null) {
            $this->throwConfigurationException('Invalid start date format. Needs to be ISO-8601 string or DateTime object');
        }

        if ($this->type->is(TypeEnum::CALENDAR()) && $this->fyStartDate->format('md') == '0229') {
            $this->throwConfigurationException('This library does not support 29th of February as the starting date for calendar type financial year');
        }

        // Set date to start of the day.
        $this->fyStartDate->setTime(0,0,0,0);

        // If this method was not called on instantiation,
        // recalculate financial year end date from current settings,
        // even if the new start date is the same as the previous one (why re-setting the same date?).
        if ($originalFyStartDate !== null) {
            $this->setFyEndDate(null);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTimeInterface|\DateTimeImmutable
     */
    public function getFyEndDate()
    {
        return $this->fyEndDate;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTime|\DateTimeImmutable|string|null $date
     */
    public function setFyEndDate($date = null)
    {
        // If date param is null, we set end date relative to start date (it is already set from the constructor).
        // At this point start date's time is set to 00:00:00
        if ($date === null) {
            // We will set end date from the start date object which should be present.
            // fyStartDate is an Immutable object so we can safely copy.
            $this->fyEndDate = $this->fyStartDate;

            // For calendar type, the end date is 1 year, minus 1 day after the start date.
            if ($this->type->is(TypeEnum::CALENDAR())) {
                $this->fyEndDate->modify('+1 year')
                                ->modify('-1 day');

            }

            if ($this->type->is(TypeEnum::BUSINESS())) {
                // As a financial year would have 52 or 53 weeks, the param handles it.
                $this->fyEndDate->modify('+' . (string) $this->fyWeeks . 'week')
                                ->modify('-1 day');
            }

            // On null param, there is no need for extra logic.
            // The financial year end date property has been computed according to existing settings.
            return;
        }

        // Placeholder.
        $fyEndDate = null;

        if ($date instanceof \DateTime) {
            $fyEndDate = \DateTimeImmutable::createFromMutable($date);
        }

        if ($date instanceof \DateTimeImmutable) {
            $fyEndDate = $date;
        }

        if (is_string($date)) {
            $fyEndDate = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        }

        if (!$fyEndDate || $fyEndDate === null) {
            $this->throwConfigurationException('Invalid end date format. Needs to be ISO-8601 string or DateTime object');
        }

        // Set date to start of the day.
        $fyEndDate->setTime(0,0,0,0);

        // Safe copy as fyEndDate is an immutable object.
        $pendingValidationFyEndDate = $fyEndDate;

        // If the financial year type is calendar, Check that is correctly set.
        if ($this->type->is(TypeEnum::CALENDAR())) {

            $diff = $this->fyStartDate->diff($pendingValidationFyEndDate->modify('+1 day'));

            // Check that end date + 1 day is exactly 1 year after the start date.
            if ($diff->y === 1 && $diff->m === 0 && $diff->d === 0) {
                $this->fyEndDate = $fyEndDate;

                return;
            }

            $this->throwConfigurationException('The provided end date can not be validated against the start date');
        }

        // Validate financial year end date if current method is not called on instantiation.
        // Set fyWeeks on success.
        if ($this->type->is(TypeEnum::BUSINESS())) {

            $diff = $this->fyStartDate->diff($pendingValidationFyEndDate->modify('+1 day'))->days / 7;

            if ($diff !== 52 || $diff !== 53) {
                $this->throwConfigurationException('The provided end date can not be validated against the start date');
            }

            $this->fyWeeks = $diff;
        }

        $this->fyEndDate = $fyEndDate;
    }

    /**
     * {@inheritdoc}
     *
     * @param  \DateTimeInterface|\DateTime|\DateTimeImmutable $startDate
     * @param  \DateTimeInterface|\DateTime|\DateTimeImmutable $endDate
     *
     */
    public function getPeriodIdByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        // TODO: Implement getPeriodIdByDateRange() method.
    }

    /**
     * @inheritdoc
     *
     * @param  \DateTimeInterface|\DateTime|\DateTimeImmutable $startDate
     * @param  \DateTimeInterface|\DateTime|\DateTimeImmutable $endDate
     *
     */
    public function getBusinessWeekIdByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        // TODO: Implement getBusinessWeekIdByDateRange() method.
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getPeriodById(int $id)
    {
        $this->validate();

        $this->validatePeriodId($id);

        $period = null;

        // Safe as immutable.
        $fyStartDate = $this->fyStartDate;

        // In calendar type, periods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->type->is(TypeEnum::CALENDAR())) {
            // If 1st period, no need for modification of start date.
            $periodStartDate = $id === 1 ?
                $this->fyStartDate :
                $fyStartDate->modify('+ ' . (string) $id - 1 . ' month');

            // If last period details requested, the end date is the financial year end date.
            $periodEndDate = $id === 12 ?
                $this->fyEndDate :
                $fyStartDate->modify('+ ' . (string) $id . ' month')
                            ->modify('-1 day');

            $period = new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {

            // If 1st period, no need for modification of start date.
            $periodStartDate = $id === 1 ?
                $this->fyStartDate :
                $fyStartDate->modify('+ ' . (string) (($id - 1) * 4) . ' week');

            // If last period details requested, the end date is the financial year end date.
            $periodEndDate = $id === 12 ?
                $this->fyEndDate :
                $fyStartDate->modify('+ ' . (string) ($id * 4) . ' week')
                            ->modify('-1 day');

            $period = new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
        }

        if ($period === null) {
            throw new Exception('A date range period could not be set');
        }

        return $period;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getBusinessWeekById(int $id)
    {
        $this->validate();

        if ($this->type->isNot(TypeEnum::BUSINESS())) {
            $this->throwConfigurationException('Week date range is not applicable for non business type financial year');
        }

        if ($id < 1 || $id > $this->fyWeeks) {
            throw new Exception('There is no week with id: ' . $id);
        }

        // Safe copy as immutable.
        $dateTime = $this->fyStartDate;

        // If 1st week, get the start of the financial year.
        $periodStartDate = $id === 1 ?
            $this->fyStartDate :
            $dateTime->modify('+' . (string) $id - 1 . ' week');

        // If last week, get the end of the financial year.
        $periodEndDate = $id === $this->fyWeeks ?
            $this->fyEndDate :
            $dateTime->modify('+6 day');

        return new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
    }

    /**
     * {@inheritdoc}
     *
     * Get the first date of the period with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getFirstDateOfPeriodById(int $id)
    {
        $this->validate();

        $this->validatePeriodId($id);

        $periodStart = null;

        // If 1st period, get the start of the financial year, regardless of the type.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        // Safe as immutable.
        $fyStartDate = $this->fyStartDate;

        // In calendar type, periods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->type->is(TypeEnum::CALENDAR())) {
            $periodStart = $fyStartDate->modify('+ ' . (string) $id - 1 . ' month');
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {
            $periodStart = $fyStartDate->modify('+ ' . (string) (($id - 1) * 4) . ' week');
        }

        if ($periodStart === null) {
            throw new Exception('Could not calculate period start date');
        }

        return $periodStart;
    }

    /**
     * {@inheritdoc}
     *
     * Get the last date of the period with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface|\DateTimeImmutable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getLastDateOfPeriodById(int $id)
    {
        $this->validate();

        $this->validatePeriodId($id);

        $periodEnd = null;

        // If last period, get the end of the financial year, regardless of the type.
        if ($id === 12) {
            return $this->fyEndDate;
        }

        // Safe as immutable.
        $fyStartDate = $this->fyStartDate;

        // In calendar type, periods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->type->is(TypeEnum::CALENDAR())) {
            $periodEnd = $fyStartDate->modify('+ ' . (string) $id. ' month')
                                     ->modify('-1 day');
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {
            $periodEnd = $fyStartDate->modify('+ ' . (string) (($id) * 4) . ' week')
                                     ->modify('-1 day');
        }

        if ($periodEnd === null) {
            throw new Exception('Could not calculate period end date');
        }

        return $periodEnd;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTimeInterface|\DateTimeImmutable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getFirstDateOfBusinessWeekById(int $id)
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If 1st week, get the start of the financial year.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        // Safe copy as immutable.
        $dateTime = $this->fyStartDate;

        return $dateTime->modify('+ ' . (string) $id - 1 . ' week');
    }

    /**
     * {@inheritdoc}
     *
     * Get the last date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface|\DateTimeImmutable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getLastDateOfBusinessWeekById(int $id)
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If last week, get the end of the financial year.
        if ($id === $this->fyWeeks) {
            return $this->fyEndDate;
        }

        // Safe copy as immutable.
        $dateTime = $this->fyEndDate;

        return $dateTime->modify('+ ' . (string) $id . ' week')
                        ->modify('-1 day');
    }

    /**
     * {@inheritdoc}
     *
     * Get the date range of the first business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getFirstBusinessWeekByPeriodId(int $id)
    {
        return $this->getBusinessWeekById(($id - 1) * 4);
    }

    /**
     * {@inheritdoc}
     *
     * Get the date range of the second business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getSecondBusinessWeekByPeriodId(int $id)
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 2);
    }

    /**
     * {@inheritdoc}
     *
     * Get the date range of the third business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getThirdBusinessWeekOfPeriodId(int $id)
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 3);
    }

    /**
     * {@inheritdoc}
     *
     * Get the date range of the fourth business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getFourthBusinessWeekByPeriodId(int $id)
    {
        return $this->getBusinessWeekById($id * 4);
    }

    /**
     * @return  \Traversable
     *
     * @throws  Exception
     * @throws  ConfigException
     */
    public function getFifthThirdBusinessWeekPeriod()
    {
        return $this->getBusinessWeekById(53);
    }

    /**
     * Validate period $id is between 1 and 12.
     *
     * @param  int $id
     *
     * @return void
     *
     * @throws Exception
     */
    protected function validatePeriodId(int $id)
    {
        if ($id < 1 || $id > 12) {
            throw new Exception('There is no period with id: ' . $id);
        }
    }

    /**
     * Validate fyType is business and week $id is between 1 and the set fyWeeks (52 or 53).
     *
     * @param  int $id
     *
     * @throws Exception
     * @throws ConfigException
     */
    protected function validateBusinessWeekId(int $id)
    {
        if ($this->type->isNot(TypeEnum::BUSINESS())) {
            $this->throwConfigurationException('Week date range is not applicable for non business type financial year');
        }

        if ($id < 1 || $id > $this->fyWeeks) {
            throw new Exception('There is no week with id: ' . $id);
        }
    }
}