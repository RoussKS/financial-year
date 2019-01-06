<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\FinancialYear\Enums\TypeEnum;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Interfaces\AdapterInterface;
use function Sodium\add;

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
     * @throws Exception
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
     *
     * @throws Exception
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
     *
     * @throws Exception
     */
    public function setFyStartDate($date)
    {
        // fyStartDate property is an immutable object.
        $originalFyStartDate = $this->fyStartDate;

        $this->fyStartDate = $this->getDateObject($date);

        if ($this->type->is(TypeEnum::CALENDAR()) && $this->fyStartDate->format('md') == '0229') {
            $this->throwConfigurationException('This library does not support 29th of February as the starting date for calendar type financial year');
        }

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
     *
     * @throws Exception
     * @throws \Exception
     */
    public function setFyEndDate($date = null)
    {
        // If date param is null, we set end date relative to start date (it is already set from the constructor).
        // At this point start date's time is set to 00:00:00
        if ($date === null) {
            // We will set end date from the start date object which should be present.

            // For calendar type, the end date is 1 year, minus 1 day after the start date.
            if ($this->type->is(TypeEnum::CALENDAR())) {
                $this->fyEndDate = $this->fyStartDate->add(\DateInterval::createFromDateString('P1Y'))
                                                     ->sub(\DateInterval::createFromDateString('P1D'));

            }

            // For business type, the end date is the number of weeks , minus 1 day after the start date.
            if ($this->type->is(TypeEnum::BUSINESS())) {
                // As a financial year would have 52 or 53 weeks, the param handles it.
                $this->fyEndDate = $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) $this->fyWeeks . 'W'))
                                                     ->sub(\DateInterval::createFromDateString('P1D'));
            }

            // On null param, there is no need for extra logic.
            // The financial year end date property has been computed according to existing settings.
            return;
        }

        // Safe as Immutable.
        $dateTime = $this->getDateObject($date);

        // If the financial year type is calendar, Check that is correctly set.
        if ($this->type->is(TypeEnum::CALENDAR())) {

            $diff = $this->fyStartDate->diff($dateTime->add(\DateInterval::createFromDateString('P1D')));

            // Check that end date + 1 day (start date of next financial year) is exactly 1 year after the start date.
            if ($diff->y !== 1 && $diff->m !== 0 && $diff->days !== 0) {
                $this->throwConfigurationException('The provided end date can not be validated against the start date');
            }
        }

        // If the financial year type is business, Check that is correctly set.
        // Set fyWeeks on success.
        if ($this->type->is(TypeEnum::BUSINESS())) {

            $diff = $this->fyStartDate->diff($dateTime->add(\DateInterval::createFromDateString('P1D')))->days / 7;

            // Check that end date + 1 day (start date of next financial year) is exactly 52 or 53 weeks after the start date.
            if ($diff !== 52 || $diff !== 53) {
                $this->throwConfigurationException('The provided end date can not be validated against the start date');
            }

            $this->fyWeeks = $diff;
        }

        // The above conditions cover all types (which is strictly set on instantiation, so we can safely set the fyEndDate.
        $this->fyEndDate = $dateTime;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DatePeriod
     *
     * @throws \Exception
     * @throws Exception
     */
    public function getPeriodById(int $id)
    {
        $this->validate();

        $this->validatePeriodId($id);

        $period = null;

        // In calendar type, periods are always 12 as the months, regardless of the start date within the month.
        if ($this->type->is(TypeEnum::CALENDAR())) {
            // If 1st period, no need for modification of start date.
            $periodStartDate = $id === 1 ?
                $this->fyStartDate :
                $this->fyStartDate->add(new \DateInterval('P' . (string) $id - 1 . 'M'));

            // If last period details requested, the end date is the financial year end date.
            // Else, it's the end of the month.
            $periodEndDate = $id === 12 ?
                $this->fyEndDate :
                $periodStartDate->add(\DateInterval::createFromDateString('P1M'))
                                ->sub(\DateInterval::createFromDateString('P1D'));

            $period = new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {
            // If 1st period, no need for modification of start date.
            $periodStartDate = $id === 1 ?
                $this->fyStartDate :
                $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) (($id - 1) * 4) . 'W'));

            // If last period details requested, the end date is the financial year end date.
            // This way we also overcome the potential issue of a 53rd week.
            $periodEndDate = $id === 12 ?
                $this->fyEndDate :
                $periodStartDate->add(\DateInterval::createFromDateString('P4W'))
                                ->sub(\DateInterval::createFromDateString('P1D'));

            $period = new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
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
     * @return \DatePeriod
     *
     * @throws Exception
     */
    public function getBusinessWeekById(int $id)
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If 1st week, get the start of the financial year.
        $weekStartDate = $id === 1 ?
            $this->fyStartDate :
            $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) $id - 1 . 'W'));

        // If last week, get the end of the financial year.
        $weekEndDate = $id === $this->fyWeeks ?
            $this->fyEndDate :
            $weekStartDate->add(\DateInterval::createFromDateString('P6D'));

        return new \DatePeriod($weekStartDate, \DateInterval::createFromDateString('P1D'), $weekEndDate);
    }

    /**
     * {@inheritdoc}
     *
     * @param  \DateTime|\DateTimeImmutable|string $date
     *
     * @throws Exception
     */
    public function getPeriodIdByDate($date)
    {
        $dateTime = $this->getDateObject($date);

        for ($id = 1; $id <= 12; $id++) {
            /** @var \DateTimeInterface $interval */
            foreach ($this->getPeriodById($id) as $interval) {
                if ($dateTime->format('Y-m-d') === $interval->format('Y-m-d')) {
                    return $id;
                }
            }
        }

        throw new Exception('A period could not be found for the specified date');
    }

    /**
     * {@inheritdoc}
     *
     * @param  \DateTime|\DateTimeImmutable|string $date
     *
     * @throws Exception
     */
    public function getBusinessWeekIdIdByDate($date)
    {
        $dateTime = $this->getDateObject($date);

        for ($id = 1; $id <= $this->fyWeeks; $id++) {
            /** @var \DateTimeInterface $interval */
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
    public function getFirstDateOfPeriodById(int $id)
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
        if ($this->type->is(TypeEnum::CALENDAR())) {
            $periodStart = $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) $id - 1 . 'M'));
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {
            $periodStart = $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) (($id - 1) * 4) . 'W'));
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
    public function getLastDateOfPeriodById(int $id)
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
        if ($this->type->is(TypeEnum::CALENDAR())) {
            $periodEnd = $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) $id . 'M'))
                                           ->sub(\DateInterval::createFromDateString('P1D'));
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {
            $periodEnd = $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) (($id) * 4) . 'W'))
                                           ->sub(\DateInterval::createFromDateString('P1D'));
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
     */
    public function getFirstDateOfBusinessWeekById(int $id)
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If 1st week, get the start of the financial year.
        if ($id === 1) {
            return $this->fyStartDate;
        }

        return $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) $id - 1 . 'W'));
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTimeInterface|\DateTimeImmutable
     *
     * @throws Exception
     */
    public function getLastDateOfBusinessWeekById(int $id)
    {
        $this->validate();

        $this->validateBusinessWeekId($id);

        // If last week, get the end of the financial year.
        if ($id === $this->fyWeeks) {
            return $this->fyEndDate;
        }

        return $this->fyStartDate->add(\DateInterval::createFromDateString('P' . (string) $id . 'W'))
                                 ->sub(\DateInterval::createFromDateString('P1D'));
    }

    /**
     * {@inheritdoc}
     *
     * @return \DatePeriod
     *
     * @throws Exception
     */
    public function getFirstBusinessWeekByPeriodId(int $id)
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 1);
    }

    /**
     * {@inheritdoc}
     *
     * @return \DatePeriod
     *
     * @throws Exception
     */
    public function getSecondBusinessWeekByPeriodId(int $id)
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 2);
    }

    /**
     * {@inheritdoc}
     *
     * @return \DatePeriod
     *
     * @throws Exception
     */
    public function getThirdBusinessWeekOfPeriodId(int $id)
    {
        return $this->getBusinessWeekById(($id - 1) * 4 + 3);
    }

    /**
     * {@inheritdoc}
     *
     * @return \DatePeriod
     *
     * @throws Exception
     */
    public function getFourthBusinessWeekByPeriodId(int $id)
    {
        return $this->getBusinessWeekById($id * 4);
    }

    /**
     * @return \DatePeriod
     *
     * @throws  Exception
     * @throws  ConfigException
     */
    public function getFifthThirdBusinessWeekPeriod()
    {
        return $this->getBusinessWeekById(53);
    }

    /**
     * Get a date object from the provided param.
     *
     * @param  \DateTime|\DateTimeImmutable|string $date$date
     *
     * @return \DateTimeImmutable
     *
     * @throws Exception
     */
    protected function getDateObject($date)
    {
        // Placeholder
        $dateTime = null;

        if ($date instanceof \DateTime) {
            $dateTime = \DateTimeImmutable::createFromMutable($date)->setTime(0,0,0,0);
        }

        if ($date instanceof \DateTimeImmutable) {
            $dateTime = $date->setTime(0,0,0,0);
        }

        if (is_string($date)) {
            $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $date)->setTime(0,0,0,0);
        }

        if (!$dateTime || $dateTime === null) {
            throw new Exception('Invalid date format. Needs to be ISO-8601 string or DateTime/DateTimeImmutable object');
        }

        // Set date object to start of the day.
        return $dateTime;
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
            $this->throwConfigurationException('Week id is not applicable for non business type financial year');
        }

        if ($id < 1 || $id > $this->fyWeeks) {
            throw new Exception('There is no week with id: ' . $id);
        }
    }
}