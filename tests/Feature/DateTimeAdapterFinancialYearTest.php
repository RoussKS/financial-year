<?php

namespace RoussKS\FinancialYear\Tests\Feature;

use DateTime;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\FinancialYear;
use RoussKS\FinancialYear\Tests\BaseTestCase;

class DateTimeAdapterFinancialYearTest extends BaseTestCase
{
    /**
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertExceptionOnInvalidPeriodIdForCalendarTypeFinancialYear(): void
    {
        $startDate = new DateTime('2019-01-01');

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $startDate,
        ];

        /** \RoussKS\FinancialYear\Interfaces\AdapterInterface $fy */
        $fy = (new FinancialYear($startDate, $config))->getAdapter();

        $fyPeriodsArray = [];

        for ($i = 1; $i <= $fy->getFyPeriods(); $i++) {
            $fyPeriodsArray[] = $i;
        }

        do {
            $randomPeriodId = random_int(-1000, 1000);
        } while(in_array($randomPeriodId, $fyPeriodsArray, true));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no period with id: ' . $randomPeriodId);

        // A Calendar Type Financial Year has 12 periods only.
        $fy->getPeriodById($randomPeriodId);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertExceptionOnInvalidPeriodIdForBusinessTypeFinancialYear(): void
    {
        $startDate = new DateTime('2019-01-01');

        $config = [
            'fyType' => 'business',
            'fyStartDate' => $startDate,
        ];

        /** \RoussKS\FinancialYear\Interfaces\AdapterInterface $fy */
        $fy = (new FinancialYear($startDate, $config))->getAdapter();

        $fyPeriodsArray = [];

        for ($i = 1; $i <= $fy->getFyPeriods(); $i++) {
            $fyPeriodsArray[] = $i;
        }

        do {
            $randomPeriodId = random_int(-1000, 1000);
        } while(in_array($randomPeriodId, $fyPeriodsArray, true));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no period with id: ' . $randomPeriodId);

        // A Calendar Type Financial Year has 12 periods only.
        $fy->getPeriodById($randomPeriodId);
    }
}