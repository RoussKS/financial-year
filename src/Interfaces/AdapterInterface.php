<?php

namespace RoussKS\FinancialYear\Interfaces;

use RoussKS\FinancialYear\Exceptions\ConfigException;

/**
 * Interface AdapterInterface
 *
 * @package RoussKS\FinancialYear\Interfaces
 */
interface AdapterInterface
{
    /**
     * @param  string $type
     *
     * @return void
     * 
     * @throws ConfigException
     */
    public function setType($type);

    /**
     * @return void
     *
     * @throws ConfigException
     */
    public function validate();

    /**
     * @param  mixed $date
     *
     * @return mixed
     */
    public function setStartOfTheYear($date);

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getDayTypePeriodId();

    /**
     * @return int
     *
     * @throws ConfigException
     */
    public function getDayTypeWeekId();
}