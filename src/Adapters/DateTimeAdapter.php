<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\Enums\TypeEnum;
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
    /** @var \DateTime */
    protected $fyStartDate;

    /** @var  \DateTime */
    protected $fyEndDate;

    public function __construct(
        \DateTime $fy,
        string $fyType,
        string $fyStartDate,
        string $fyEndDate = null,
        bool $fiftyThreeWeeks = false
    ) {
        $this->fy = $fy;

        if ($fyStartDate !== null) {
            $this->setFyStartDate($fyStartDate);
        }

        if ($fyEndDate !== null) {
            $this->setFyEndDate($fyEndDate);
        }

        parent::__construct($fyType);
    }

    /**
     * @return \DateTime|null
     */
    public function getFyStartDate()
    {
        return $this->fyStartDate;
    }

    /**
     * @param string|\DateTime $date
     *
     * {@inheritdoc}
     */
    public function setFyStartDate($date)
    {
        if ($date instanceof \DateTime) {
            $this->fyStartDate = $date;

            return;
        }

        $this->fyStartDate = \DateTime::createFromFormat('Y-m-d', $date);

        if (!$this->fyStartDate || $this->fyStartDate === null) {
            $this->throwConfigurationException('Invalid start date format. Needs to be ISO-8601 string or DateTime object');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTime
     */
    public function getFyEndDate()
    {
        return $this->fyEndDate;
    }

    /**
     * @param string|\DateTime|null $date
     *
     * {@inheritdoc}
     *
     * @throws ConfigException
     */
    public function setFyEndDate($date = null, $fiftyThreeWeeks = false)
    {
        if ($date === null) {
            if ($this->fyStartDate === null) {
                $this->throwConfigurationException('Can not set end date without a start date');
            }

            // We will set end date from the start date object which should be present.
            $this->fyEndDate = clone $this->fyStartDate;

            // For calendar type, the end date is 1 year, minus 1 day after the start date.
            if ($this->getType()->is(TypeEnum::CALENDAR())) {
                $this->fyEndDate->modify('+1 year')
                                ->modify('-1 day');

                return;
            }

            if ($this->getType()->is(TypeEnum::BUSINESS())) {
                $this->setFyWeeks($fiftyThreeWeeks ? 53 : 52);

                // As a financial year would have 52 or 53 weeks, the param handles it.
                $this->fyEndDate->modify('+' . (string) $this->getFyWeeks() . 'week')
                                ->modify('-1 day');
            }

            return;
        }

        if ($date instanceof \DateTime) {
            $this->fyEndDate = $date;

            return;
        }

        $this->fyEndDate = \DateTime::createFromFormat('Y-m-d', $date);

        if (!$this->fyEndDate || $this->fyEndDate === null) {
            $this->throwConfigurationException('Invalid start date format. Needs to be ISO-8601 string or DateTime object');
        }
    }

    /**
     * @param  \DateTimeInterface $startDate
     * @param  \DateTimeInterface $endDate
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getPeriodIdByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        // TODO: Implement getPeriodIdByDateRange() method.
    }

    /**
     * @param  \DateTimeInterface $startDate
     * @param  \DateTimeInterface $endDate
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getBusinessWeekIdByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        // TODO: Implement getBusinessWeekIdByDateRange() method.
    }

    /**
     * Get the date range of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws Exception
     * @throws ConfigException
     */
    public function getPeriodById(int $id)
    {
        $this->validate();

        if ($id > 12 || $id < 1) {
            throw new Exception('There is no period with id: ' . $id);
        }

        $period = null;

        // In case of immutable, use unchanged.
        $dateTime = $this->fyStartDate;

        // If not immutable, get a clone
        if (!$this->fyStartDate instanceof \DateTimeImmutable) {
            $dateTime = clone $this->fyStartDate;
        }

        // In calendar type, periods are always 12 as the months,
        // regardless of the start date within the month.
        if ($this->type->is(TypeEnum::CALENDAR())) {

            $periodStart = $id - 1;
            // If 1st period, no need for modification of start date.
            $periodStartDate = $periodStart === 0 ?
                $dateTime :
                $dateTime->modify('+ ' . (string) $periodStart . ' month');

            // If last period, use year modification for end date as faster.
            $periodEnd = $id === 12 ? '+ 1 year' : '+ ' . (string) $id . ' month';
            $periodEndDate = $dateTime->modify($periodEnd)
                                      ->modify('-1 day');

            $period = new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
        }

        if ($this->type->is(TypeEnum::BUSINESS())) {

            $periodStart = $id - 1;
            // If 1st period, no need for modification of start date.
            $periodStartDate = $periodStart === 0 ?
                $dateTime :
                $dateTime->modify('+ ' . (string) ($periodStart * 4) . ' week');

            $periodEnd = '+ ' . $id === 12 ? (string) $this->fyWeeks : (string) ($id * 4) . ' week';
            $periodEndDate = $dateTime->modify($periodEnd)
                                      ->modify('-1 day');

            $period = new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
        }

        if ($period === null) {
            throw new Exception('A date range period could not be set');
        }

        return $period;
    }

    /**
     * Get the date range of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getBusinessWeekById(int $id)
    {
        $this->validate();

        if ($this->type->isNot(TypeEnum::BUSINESS())) {
            $this->throwConfigurationException('Week date range is not applicable for non business type financial year.');
        }

        $dateTime = clone $this->fyStartDate;
        $periodStartDate = $dateTime->modify('+' . (string) $id . ' week');
        $periodEndDate = $dateTime->modify('+6 day');

        return new \DatePeriod($periodStartDate, \DateInterval::createFromDateString('P1D'), $periodEndDate);
    }

    /**
     * Get the first date of the period with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getFirstDateOfPeriodById(int $id)
    {
        // TODO: Implement getFirstDateOfPeriodById() method.
    }

    /**
     * Get the last date of the period with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getLastDateOfPeriodById(int $id)
    {
        // TODO: Implement getLastDateOfPeriodById() method.
    }

    /**
     * Get the first date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getFirstDateOfBusinessWeekById(int $id)
    {
        // TODO: Implement getFirstDateOfBusinessWeekById() method.
    }

    /**
     * Get the last date of the business week with the given id.
     *
     * @param  int $id
     *
     * @return \DateTimeInterface
     *
     * @throws ConfigException
     */
    public function getLastDateOfBusinessWeekById(int $id)
    {
        // TODO: Implement getLastDateOfBusinessWeekById() method.
    }

    /**
     * Get the date range of the first business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getFirstBusinessWeekByPeriodId(int $id)
    {
        // TODO: Implement getFirstBusinessWeekByPeriodId() method.
    }

    /**
     * Get the date range of the second business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getSecondBusinessWeekByPeriodId(int $id)
    {
        // TODO: Implement getSecondBusinessWeekByPeriodId() method.
    }

    /**
     * Get the date range of the third business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getThirdBusinessWeekOfPeriodId(int $id)
    {
        // TODO: Implement getThirdBusinessWeekOfPeriodId() method.
    }

    /**
     * Get the date range of the fourth business week of the period with the given id.
     *
     * @param  int $id
     *
     * @return \Traversable
     *
     * @throws ConfigException
     */
    public function getFourthBusinessWeekByPeriodId(int $id)
    {
        // TODO: Implement getFourthBusinessWeekByPeriodId() method.
    }
}