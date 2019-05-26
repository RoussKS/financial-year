<?php

namespace RoussKS\FinancialYear\Tests\Unit\Adapters;

use DateTimeImmutable;
use RoussKS\FinancialYear\Adapters\AbstractAdapter;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function settingSameFyWeeksSetsWeeksWithoutChangingEndDateForBusinessType(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function settingDifferentFyWeeksSetsWeeksWithDifferentEndDateForBusinessType(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetFyStartDateReturnsDateTimeImmutableObject(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertSetFyStartDateThrowsExceptionForSingleInvalidDate(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertSetFyStartDateSetsNewFyEndDateIfFyStartDateChanges(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetFyEndDateReturnsDateTimeImmutableObject(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear(): void
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
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetFirstPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            $this->faker->boolean
        );

        // 1st Period should be 2019-01-1 - 2019-01-31
        $period = $dateTimeAdapter->getPeriodById(1);

        $this->assertEquals('2019-01-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-01-31 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            $this->faker->boolean
        );

        // Last Period, 12th for calendar type, should be 2019-12-01 - 2019-12-31
        $period = $dateTimeAdapter->getPeriodById(12);

        $this->assertEquals('2019-12-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-12-31 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2nd Period should be 2019-01-29 - 2019-02-26
        $period = $dateTimeAdapter->getPeriodById(2);

        $this->assertEquals('2019-01-29 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-02-25 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetFirstPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 1st Period should be 2019-01-01 - 2019-01-28
        $period = $dateTimeAdapter->getPeriodById(1);

        $this->assertEquals('2019-01-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-01-28 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYearFiftyTwoWeeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            false
        );

        // Last Period, 13th for business type, should be 2019-12-02 - 2019-12-30 for 52 weeks year.
        $period = $dateTimeAdapter->getPeriodById(13);

        $this->assertEquals('2019-12-03 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-12-30 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYearFiftyThreeWeeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            true
        );

        // Last Period, 13th for business type, should be 2019-12-02 - 2020-01-06 for 53 weeks year.
        $period = $dateTimeAdapter->getPeriodById(13);

        $this->assertEquals('2019-12-03 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2020-01-06 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekByIdThrowsExceptionOnNonBusinessTypeFinancialYearType(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Week id is not applicable for non business type financial year');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            true
        );

        $dateTimeAdapter->getBusinessWeekById(1);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     * @throws \Exception
     */
    public function assertGetBusinessWeekByIdThrowsExceptionOnInvalidWeekId(): void
    {
        $this->expectException(Exception::class);

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // Build an array of integers of the financial year weeks.
        $fyWeeksArray = [];

        for ($i = 1; $i <= $dateTimeAdapter->getFyWeeks(); $i++) {
            $fyWeeksArray[] = $i;
        }

        // Get a random week id that's not equal to the available weeks.
        do {
            $randomWeekId = random_int(-1000, 1000);
        } while(in_array($randomWeekId, $fyWeeksArray, true));

        // Set the expected message after we have set the financial year weeks
        $this->expectExceptionMessage('There is no week with id: ' . $randomWeekId);

        $dateTimeAdapter->getBusinessWeekById($randomWeekId);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekByIdReturnsCorrectWeekPeriodForBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2nd week should be 2019-01-08 - 2019-01-14.
        $week = $dateTimeAdapter->getBusinessWeekById(2);

        $this->assertEquals('2019-01-08 00:00:00', $week->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-01-14 00:00:00', $week->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekByIdReturnsCorrectWeekPeriodForFirstWeekOfBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // First week should be 2019-01-01 - 2019-01-07.
        $firstWeek = $dateTimeAdapter->getBusinessWeekById(1);

        $this->assertEquals('2019-01-01 00:00:00', $firstWeek->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-01-07 00:00:00', $firstWeek->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekByIdReturnsCorrectWeekPeriodForLastWeekOfBusinessTypeFinancialYearFiftyTwoWeeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            false
        );

        // Last week should be 2019-12-24 - 2019-12-30 for 52 weeks year.
        $lastWeek = $dateTimeAdapter->getBusinessWeekById(52);

        $this->assertEquals('2019-12-24 00:00:00', $lastWeek->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2019-12-30 00:00:00', $lastWeek->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekByIdReturnsCorrectWeekPeriodForLastWeekOfBusinessTypeFinancialYearFiftyThreeWeeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            true
        );

        // Last week should be 2019-12-31 - 2020-01-06 for 53 weeks year.
        $lastWeek = $dateTimeAdapter->getBusinessWeekById(53);

        $this->assertEquals('2019-12-31 00:00:00', $lastWeek->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals('2020-01-06 00:00:00', $lastWeek->getEndDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetPeriodIdByDateThrowsExceptionOnDateBeforeFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            '2019-01-01',
            $this->faker->boolean
        );

        $dateTimeAdapter->getPeriodIdByDate('2018-12-31');
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetPeriodIdByDateThrowsExceptionOnDateAfterFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            '2019-01-01',
            $this->faker->boolean
        );

        // 2020-01-07 is out of range even if the type is business and weeks 53, if start date is 2019-01-01
        $dateTimeAdapter->getPeriodIdByDate('2020-01-07');
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetPeriodIdByDateReturnsCorrectIdForDate(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            '2019-01-01',
            $this->faker->boolean
        );

        // 2019-02-07 belongs to 2nd period for both types
        $this->assertEquals(2, $dateTimeAdapter->getPeriodIdByDate('2019-02-07'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekIdByDateThrowsExceptionOnDateBeforeFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        $dateTimeAdapter->getBusinessWeekIdIdByDate('2018-12-31');
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekIdByDateThrowsExceptionOnDateAfterFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2020-01-07 is out of range even if the type is business and weeks 53, if start date is 2019-01-01
        $dateTimeAdapter->getBusinessWeekIdIdByDate('2020-01-07');
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetBusinessWeekIdByDateReturnsCorrectIdForDate(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            $this->faker->boolean
        );

        // 2019-01-31 belongs to 5th week
        $this->assertEquals(5, $dateTimeAdapter->getBusinessWeekIdIdByDate('2019-01-31'));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertGetFirstDateOfPeriodByIdReturnsFinancialYearStartDateForFirstPeriod(): void
    {
        $randomDateTime = $this->faker->dateTime;

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes,1)],
            $randomDateTime,
            $this->faker->boolean
        );

        // Now set random datetime to start of day for checking with resulting start date.
        $randomDateTime->setTime(0, 0);

        $this->assertEquals(
            $randomDateTime->format('Y-m-d H:i:s'),
            $dateTimeAdapter->getFirstDateOfPeriodById(1)->format('Y-m-d H:i:s')
        );
    }
}