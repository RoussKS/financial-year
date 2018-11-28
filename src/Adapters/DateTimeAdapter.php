<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Interfaces\AdapterInterface;

/**
 * Implementation of PHP's DateTime FinancialYear Adapter
 *
 * Class DateTimeAdapter
 *
 * @package RoussKS\FinancialYear\Adapters
 */
class DateTimeAdapter extends AbstractAdapter implements AdapterInterface
{
    public function __construct(
        \DateTime $fy,
        string $type = null,
        string $startDate = null,
        string $endDate = null
    ) {
        if ($fy === null) {
            $this->throwConfigurationException();
        }

        $this->fy = $fy;

        parent::__construct($type, $startDate, $endDate);
    }
    
    /**
     * @param  mixed $date
     *
     * @return mixed
     */
    public function setStartOfTheYear($date)
    {
        // TODO: Implement setStartOfTheYear() method.
    }

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getDayTypePeriodId()
    {
        // TODO: Implement getDayTypePeriodId() method.
    }

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getDayTypeWeekId()
    {
        // TODO: Implement getDayTypeWeekId() method.
    }
}