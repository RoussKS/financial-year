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
            $this->faker->boolean
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeAdapter->getFyEndDate());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2nd Period should be 2019-02-1 - 2019-02-28
        $period = $dateTimeAdapter->getPeriodById(2);

        $this->assertEquals('2019-02-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-02-28 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetFirstPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2nd Period should be 2019-01-1 - 2019-01-31
        $period = $dateTimeAdapter->getPeriodById(1);

        $this->assertEquals('2019-01-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-01-31 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2nd Period should be 2019-12-01 - 2019-12-31
        $period = $dateTimeAdapter->getPeriodById(12);

        $this->assertEquals('2019-12-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-12-31 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYear()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 3rd Period should be 2019-01-29 - 2019-02-26
        $period = $dateTimeAdapter->getPeriodById(2);

        $this->assertEquals('2019-01-29 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-02-25 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetFirstPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYear()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 3rd Period should be 2019-01-01 - 2019-01-28
        $period = $dateTimeAdapter->getPeriodById(1);

        $this->assertEquals('2019-01-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-01-28 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYearFiftyTwoWeeks()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            false
        );

        // 3rd Period should be 2019-12-02 - 2019-12-30
        $period = $dateTimeAdapter->getPeriodById(12);

        $this->assertEquals('2019-12-02 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-12-30 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYearFiftyThreeWeeks()
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            true
        );

        // 3rd Period should be 2019-12-02 - 2020-01-06
        $period = $dateTimeAdapter->getPeriodById(12);

        $this->assertEquals('2019-12-02 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2020-01-06 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }
}