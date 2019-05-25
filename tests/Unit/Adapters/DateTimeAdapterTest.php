<?php

namespace RoussKS\FinancialYear\Tests\Unit\Adapters;

use DateTimeImmutable;
use RoussKS\FinancialYear\Adapters\AbstractAdapter;
use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Adapters\DateTimeAdapter;

/**
 * Class DateTimeAdapterTest
 *
 * @package RoussKS\FinancialYear\Tests\Unit\Adapters
 */
class DateTimeAdapterTest extends BaseTestCase
{
    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function settingSameFyWeeksSetsWeeksWithoutChangingEndDateForBusinessType()
    {
        $fiftyThreeWeeks = $this->faker->boolean;

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            $this->faker->dateTime,
            null,
            $fiftyThreeWeeks
        );

        $fyEndDate = $dateTimeAdapter->getFyEndDate();

        $dateTimeAdapter->setFyWeeks($fiftyThreeWeeks);

        $this->assertSame($fyEndDate->format('YmdHis'), $dateTimeAdapter->getFyEndDate()->format('YmdHis'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function settingDifferentFyWeeksSetsWeeksWithDifferentEndDateForBusinessType()
    {
        $fiftyThreeWeeks = $this->faker->boolean;

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            $this->faker->dateTime,
            null,
            $fiftyThreeWeeks
        );

        $fyEndDate = $dateTimeAdapter->getFyEndDate();

        $dateTimeAdapter->setFyWeeks(!$fiftyThreeWeeks);

        $this->assertNotSame($fyEndDate->format('YmdHis'), $dateTimeAdapter->getFyEndDate()->format('YmdHis'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetFyStartDateReturnsDateTimeImmutableObject()
    {
        $types = [AbstractAdapter::TYPE_CALENDAR, AbstractAdapter::TYPE_BUSINESS];

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            $this->faker->dateTime,
            null,
            array_rand($types,1)
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeAdapter->getFyStartDate());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetFyEndDateReturnsDateTimeImmutableObject()
    {
        $types = [AbstractAdapter::TYPE_CALENDAR, AbstractAdapter::TYPE_BUSINESS];

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            $this->faker->dateTime,
            null,
            array_rand($types,1)
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeAdapter->getFyEndDate());
    }
}