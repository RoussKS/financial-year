<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RoussKS\FinancialYear\AbstractAdapter;
use RoussKS\FinancialYear\DateTimeAdapter;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Tests\BaseTestCase;

/**
 * Class DateTimeAdapterTest
 *
 * @package RoussKS\FinancialYear\Tests\Unit
 */
class DateTimeAdapterTest extends BaseTestCase
{
    /**
     * @var array
     */
    protected $fyTypes = [AbstractAdapter::TYPE_CALENDAR, AbstractAdapter::TYPE_BUSINESS];

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     * @throws \Exception
     */
    public function settingSameFyWeeksSetsWeeksWithoutChangingEndDateForBusinessType(): void
    {
        $fiftyThreeWeeks = (bool) random_int(0, 1);

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            $this->getRandomDateTime(),
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
     * @throws \Exception
     */
    public function settingDifferentFyWeeksSetsWeeksWithDifferentEndDateForBusinessType(): void
    {
        $fiftyThreeWeeks = (bool) random_int(0, 1);

        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            $this->getRandomDateTime(),
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
     * @throws \Exception
     */
    public function assertGetFyStartDateReturnsDateTimeImmutableObject(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business' ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            (bool) random_int(0, 1)
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeAdapter->getFyStartDate());
    }

    /**
     * @test
     *
     * Invalid dates are 29, 30, 31 of any month.
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     * @throws \Exception
     */
    public function assertSetFyStartDateThrowsExceptionForInvalidDates(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'This library does not support 29, 30, 31 as start dates of a month for calendar type financial year.'
        );

        $randomDateTime = $this->getRandomDateTime();

        $datesArray = [29, 30, 31];

        // Random Year, random disallowed date. Fix to May as we know it includes all 3 dates.
        new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            $randomDateTime->format('Y') . '-05-' . $datesArray[array_rand($datesArray)],
            (bool) random_int(0, 1)
        );
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
    public function assertSetFyStartDateSetsNewFyEndDateIfFyStartDateChanges(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business' ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            (bool) random_int(0, 1)
        );

        $originalFyStartDate = $dateTimeAdapter->getFyStartDate();

        $dateTimeAdapter->setFyStartDate($this->getRandomDateExcludingDisallowedFyCalendarTypeDates());

        $this->assertNotSame(
            $originalFyStartDate->format('YmdHis'),
            $dateTimeAdapter->getFyEndDate()->format('YmdHis')
        );
    }

    /**
     * Assert DateTimeZone param is ignored if:
     * - fyStartDate param is a DateTime instance
     * - dateTimeZone param is provided.
     *
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertSetFyStartDateIgnoresDateTimeZoneParamIfStartDateParamIsDateTimeInstance(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $defaultTimeZone = new DateTimeZone('UTC');
        $timeZone = new DateTimeZone('Europe/Athens');

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business'
                ? $this->getRandomDateTime()->setTimezone($defaultTimeZone) // @phpstan-ignore-line
                : $this->getRandomDateExcludingDisallowedFyCalendarTypeDates()->setTimezone($defaultTimeZone),
            (bool) random_int(0, 1),
            $timeZone
        );

        $this->assertNotSame($timeZone->getName(), $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }

    /**
     * Assert Start Date timezone is set correctly if:
     * - fyStartDate param is a string
     * - dateTimeZone param is provided and is DateTimeZone instance.
     *
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertSetFyStartDateSetsCorrectTimeZoneIfStartDateIsStringAndDateTimeZoneInstance(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $timeZone = new DateTimeZone('Europe/Athens');

        $dateTimeAdapter = new DateTimeAdapter($type, '2023-11-19', (bool) random_int(0, 1), $timeZone);

        $this->assertSame($timeZone->getName(), $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }

    /**
     * Assert Start Date timezone is set correctly if:
     * - fyStartDate param is a string
     * - dateTimeZone param is provided and is a string of available DateTimeZones.
     *
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertSetFyStartDateSetsCorrectTimeZoneIfStartDateIsStringAndDateTimeZoneString(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $timeZone = 'Europe/Athens';

        $dateTimeAdapter = new DateTimeAdapter($type, '2023-11-19', (bool) random_int(0, 1), $timeZone);

        $this->assertSame($timeZone, $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }

    /**
     * Assert an exception is thrown on setting FY Start Date if:
     * - dateTimeZone param is provided and is a string of available DateTimeZones.
     *
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertSetFyStartDateThrowsExceptionIfInvalidDateTimeZoneStringIsProvided(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $timeZone = 'Random TimeZone';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid dateTimeZone string: ' . $timeZone);

        new DateTimeAdapter($type, '2023-11-19', (bool) random_int(0, 1), $timeZone);
    }

    /**
     * Assert an exception is thrown on setting FY Start Date if:
     * - dateTimeZone param is provided and is of an unsupported type.
     *
     * @test
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     * @throws \Exception
     */
    public function assertSetFyStartDateThrowsExceptionIfInvalidDateTimeZoneTypeIsProvided(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $timeZoneTypes = [
            new \stdClass(),
            ['something-1', 'something-2'],
            random_int(1, 100),
            (bool) random_int(0, 1)
        ];

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid dateTimeZone parameter');

        // @phpstan-ignore-next-line
        new DateTimeAdapter($type, '2023-11-19', (bool) random_int(0, 1), $timeZoneTypes[array_rand($timeZoneTypes)]);
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
    public function assertGetFyEndDateReturnsDateTimeImmutableObject(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business' ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // 2nd Period should be 2019-02-01 - 2019-02-28
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
     * @throws \Exception
     */
    public function assertGetFirstPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // 1st Period should be 2019-01-01 - 2019-01-31
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
     * @throws \Exception
     */
    public function assertGetLastPeriodByIdReturnsCorrectTimePeriodForCalendarTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetFirstPeriodByIdReturnsCorrectTimePeriodForBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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

        // Last Period, 13th for business type, should be 2019-12-03 - 2019-12-30 for 52 weeks year.
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

        // Last Period, 13th for business type, should be 2019-12-03 - 2020-01-06 for 53 weeks year.
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
        $this->expectExceptionMessage('Week id is not applicable for non business type financial year.');

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
            (bool) random_int(0, 1)
        );

        // Build an array of integers of the financial year weeks.
        $fyWeeksArray = [];

        for ($i = 1; $i <= $dateTimeAdapter->getFyWeeks(); $i++) {
            $fyWeeksArray[] = $i;
        }

        // Get a random week id that's not equal to the available weeks.
        do {
            $randomWeekId = random_int(-1000, 1000);
        } while (in_array($randomWeekId, $fyWeeksArray, true));

        // Set the expected message after we have set the financial year weeks
        $this->expectExceptionMessage('There is no week with id: ' . $randomWeekId . '.');

        $dateTimeAdapter->getBusinessWeekById($randomWeekId);
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
    public function assertGetBusinessWeekByIdReturnsCorrectWeekPeriodForBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetBusinessWeekByIdReturnsCorrectWeekPeriodForFirstWeekOfBusinessTypeFinancialYear(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetPeriodIdByDateThrowsExceptionOnDateBeforeFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes)],
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetPeriodIdByDateThrowsExceptionOnDateAfterFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes)],
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetPeriodIdByDateReturnsCorrectIdForDate(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes)],
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetBusinessWeekIdByDateThrowsExceptionOnNonBusinessTypeFinancialYear(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Business weeks are set only for a business type financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        $dateTimeAdapter->getBusinessWeekIdIdByDate('2019-01-04');
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
    public function assertGetBusinessWeekIdByDateThrowsExceptionOnDateBeforeFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetBusinessWeekIdByDateThrowsExceptionOnDateAfterFinancialYear(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetBusinessWeekIdByDateReturnsCorrectIdForDate(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
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
     * @throws \Exception
     */
    public function assertGetFirstDateOfPeriodByIdReturnsFinancialYearStartDateForFirstPeriod(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business' ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            (bool) random_int(0, 1)
        );

        $this->assertSame($dateTimeAdapter->getFyStartDate(), $dateTimeAdapter->getFirstDateOfPeriodById(1));
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
    public function assertGetFirstDateOfPeriodByIdReturnsCorrectDateForCalendarType(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        $this->assertEquals(
            '2019-04-01 00:00:00',
            $dateTimeAdapter->getFirstDateOfPeriodById(4)->format('Y-m-d H:i:s')
        );
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
    public function assertGetFirstDateOfPeriodByIdReturnsCorrectDateForBusinessType(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        $this->assertEquals(
            '2019-12-03 00:00:00',
            $dateTimeAdapter->getFirstDateOfPeriodById(13)->format('Y-m-d H:i:s')
        );
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
    public function assertGetLastDateOfPeriodByIdReturnsFinancialYearEndDateForLastPeriod(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business' ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            (bool) random_int(0, 1)
        );

        $this->assertSame(
            $dateTimeAdapter->getFyEndDate(),
            $dateTimeAdapter->getLastDateOfPeriodById($dateTimeAdapter->getFyPeriods())
        );
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
    public function assertGetLastDateOfPeriodByIdReturnsCorrectDateForCalendarType(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_CALENDAR,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        $this->assertEquals(
            '2019-04-30 00:00:00',
            $dateTimeAdapter->getLastDateOfPeriodById(4)->format('Y-m-d H:i:s')
        );
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
    public function assertGetLastDateOfPeriodByIdReturnsCorrectDateForBusinessType(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        $this->assertEquals(
            '2019-12-02 00:00:00',
            $dateTimeAdapter->getLastDateOfPeriodById(12)->format('Y-m-d H:i:s')
        );
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
    public function assertGetFirstDateOfBusinessWeekByIdReturnsFinancialYearStartDateForFirstWeek(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        $this->assertSame($dateTimeAdapter->getFyStartDate(), $dateTimeAdapter->getFirstDateOfBusinessWeekById(1));
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
    public function assertGetFirstDateOfBusinessWeekByIdReturnsCorrectDate(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Test start of week 49 (start of period 13).
        // Expect 2019-12-03.
        $this->assertEquals(
            '2019-12-03 00:00:00',
            $dateTimeAdapter->getFirstDateOfBusinessWeekById(49)->format('Y-m-d H:i:s')
        );
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
    public function assertGetLastDateOfBusinessWeekByIdReturnsFinancialYearEndDateForLastWeekWeek(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Use the weeks that are already set in the adapter.
        $this->assertSame(
            $dateTimeAdapter->getFyEndDate(),
            $dateTimeAdapter->getLastDateOfBusinessWeekById($dateTimeAdapter->getFyWeeks())
        );
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
    public function assertGetLastDateOfBusinessWeekByIdReturnsCorrectDate(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Test end of week 49.
        // Expect 2019-12-09.
        $this->assertEquals(
            '2019-12-09 00:00:00',
            $dateTimeAdapter->getLastDateOfBusinessWeekById(49)->format('Y-m-d H:i:s')
        );
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
    public function assertGetFirstBusinessWeekByPeriodIdReturnsCorrectWeek(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Test first week of period 13. That is week 49. 2019-12-03 - 2019-12-09.
        $firstBusinessWeekOfPeriod = $dateTimeAdapter->getFirstBusinessWeekByPeriodId(13);

        $this->assertEquals(
            '2019-12-03 00:00:00',
            $firstBusinessWeekOfPeriod->getStartDate()->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-12-09 00:00:00',
            $firstBusinessWeekOfPeriod->getEndDate()->format('Y-m-d H:i:s')
        );
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
    public function assertGetSecondBusinessWeekByPeriodIdReturnsCorrectWeek(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Test second week of period 12. That is week 46. 2019-11-12 - 2019-11-18.
        $secondBusinessWeekOfPeriod = $dateTimeAdapter->getSecondBusinessWeekByPeriodId(12);

        $this->assertEquals(
            '2019-11-12 00:00:00',
            $secondBusinessWeekOfPeriod->getStartDate()->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-11-18 00:00:00',
            $secondBusinessWeekOfPeriod->getEndDate()->format('Y-m-d H:i:s')
        );
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
    public function assertGetThirdBusinessWeekByPeriodIdReturnsCorrectWeek(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Test third week of period 11. That is week 43. 2019-10-22 - 2019-10-28.
        $thirdBusinessWeekOfPeriod = $dateTimeAdapter->getThirdBusinessWeekOfPeriodId(11);

        $this->assertEquals(
            '2019-10-22 00:00:00',
            $thirdBusinessWeekOfPeriod->getStartDate()->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-10-28 00:00:00',
            $thirdBusinessWeekOfPeriod->getEndDate()->format('Y-m-d H:i:s')
        );
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
    public function assertGetFourthBusinessWeekByPeriodIdReturnsCorrectWeek(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            (bool) random_int(0, 1)
        );

        // Test fourth week of period 11. That is week 44. 2019-10-29 - 2019-11-04.
        $fourthBusinessWeekOfPeriod = $dateTimeAdapter->getFourthBusinessWeekByPeriodId(11);

        $this->assertEquals(
            '2019-10-29 00:00:00',
            $fourthBusinessWeekOfPeriod->getStartDate()->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-11-04 00:00:00',
            $fourthBusinessWeekOfPeriod->getEndDate()->format('Y-m-d H:i:s')
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
    public function assertGetFiftyThirdBusinessWeekByPeriodIdReturnsCorrectWeek(): void
    {
        // Week 53 is only available for the relevant year and is the last week of the year.

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            AbstractAdapter::TYPE_BUSINESS,
            '2019-01-01',
            true
        );

        // Expect fifty third week range: 2019-12-31 - 2020-01-06
        $fiftyThreeWeek = $dateTimeAdapter->getFiftyThirdBusinessWeek();

        $this->assertEquals(
            '2019-12-31 00:00:00',
            $fiftyThreeWeek->getStartDate()->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2020-01-06 00:00:00',
            $fiftyThreeWeek->getEndDate()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @test
     *
     * Random test just to check the allowed Immutable object.
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     * @throws \Exception
     */
    public function assertGetDateObjectAcceptsImmutableParameter(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            $type,
            $type === 'business' ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            (bool) random_int(0, 1)
        );

        $this->assertNotNull($dateTimeAdapter->getFyStartDate());
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
    public function assertGetDateObjectThrowsExceptionForInvalidString(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Invalid date format. Not a valid ISO-8601 date string or DateTime/DateTimeImmutable object.'
        );

        new DateTimeAdapter(
            $this->fyTypes[array_rand($this->fyTypes)],
            bin2hex(random_bytes(20)),
            (bool) random_int(0, 1)
        );
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
    public function assertExceptionOnInvalidPeriodIdForCalendarTypeFinancialYear(): void
    {
        $startDate = new DateTime('2019-01-01');

        $fy = new DateTimeAdapter('calendar', $startDate);

        $fyPeriodsArray = [];

        for ($i = 1; $i <= $fy->getFyPeriods(); $i++) {
            $fyPeriodsArray[] = $i;
        }

        do {
            $randomPeriodId = random_int(-1000, 1000);
        } while (in_array($randomPeriodId, $fyPeriodsArray, true));

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

        $fy = new DateTimeAdapter('business', $startDate);

        $fyPeriodsArray = [];

        for ($i = 1; $i <= $fy->getFyPeriods(); $i++) {
            $fyPeriodsArray[] = $i;
        }

        do {
            $randomPeriodId = random_int(-1000, 1000);
        } while (in_array($randomPeriodId, $fyPeriodsArray, true));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no period with id: ' . $randomPeriodId . '.');

        // A Calendar Type Financial Year has 12 periods only.
        $fy->getPeriodById($randomPeriodId);
    }

    /**
     * Generate a random date excluding the ones disallowed for calendar type financial year.
     *
     * The generated date string is valid formatted so bool (false) would never be returned.
     *
     * @return DateTimeImmutable
     * @throws \Exception
     */
    protected function getRandomDateExcludingDisallowedFyCalendarTypeDates(): DateTimeInterface
    {
        $randomDateTime = $this->getRandomDateTime();

        // Get a random date string with date (day) number that does not include the disallowed dates (29, 30, 31)
        $randomDateString = $randomDateTime->format('Y') . '-' . $randomDateTime->format('m') . '-' . random_int(1, 28);

        /**
         * Type hinting that it is a valid DateTime object.
         * The random string is well formatted, so it will never return false.
         *
         * @var DateTimeImmutable $dateTime
         */
        $dateTime =  DateTimeImmutable::createFromFormat('Y-m-d', $randomDateString);

        return $dateTime;
    }
}
