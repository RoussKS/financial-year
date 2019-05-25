<?php

namespace RoussKS\FinancialYear\Tests\Unit\Adapters;

use DateTimeImmutable;
use RoussKS\FinancialYear\Adapters\AbstractAdapter;
use RoussKS\FinancialYear\Exceptions\ConfigException;
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
     * @var array
     */
    protected $fyTypes = [
        AbstractAdapter::TYPE_CALENDAR,
        AbstractAdapter::TYPE_BUSINESS
    ];

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

        // Set the opposite of original weeks.
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
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            $this->faker->dateTime,
            null,
            $this->faker->boolean
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeAdapter->getFyStartDate());
    }

    /**
     * @test
     *
     * Invalid date is 29/02 of any available year
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertSetFyStartDateThrowsExceptionForSingleInvalidDate()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'This library does not support 29th of February as the starting date for calendar type financial year'
        );

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            $this->faker->dateTime,
            null,
            $this->faker->boolean
        );

        $dateTimeAdapter->setFyStartDate('2016-02-29'); //2016-02-29 is an existing date.
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertSetFyStartDateSetsNewFyEndDateIfFyStartDateChanges()
    {
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            $this->faker->dateTime,
            null,
            $this->faker->boolean
        );

        $originalFyStartDate = $dateTimeAdapter->getFyStartDate();

        $dateTimeAdapter->setFyStartDate($this->faker->dateTime);

        $this->assertNotSame(
            $originalFyStartDate->format('YmdHis'),
            $dateTimeAdapter->getFyEndDate()->format('YmdHis')
        );
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetFyEndDateReturnsDateTimeImmutableObject()
    {
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            $this->faker->dateTime,
            null,
            $this->faker->boolean
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeAdapter->getFyEndDate());
    }
}