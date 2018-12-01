<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\Enums\TypeEnum;
use RoussKS\FinancialYear\Exceptions\ConfigException;
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
        string $type = null,
        string $fyStartDate = null,
        string $fyEndDate = null
    ) {
        if ($fy === null) {
            $this->throwConfigurationException();
        }

        $this->fy = $fy;

        if ($fyStartDate !== null) {
            $this->setFyStartDate($fyStartDate);
        }

        if ($fyEndDate !== null) {
            $this->setFyEndDate($fyEndDate);
        }

        parent::__construct($type);
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
     * @return int
     *
     * @throws ConfigException
     */
    public function getPeriodId()
    {
        // TODO: Implement getPeriodId() method.
    }

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getBusinessWeekId()
    {
        // TODO: Implement getBusinessWeekId() method.
    }
}