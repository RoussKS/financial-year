<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear\Tests\Unit;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use RoussKS\FinancialYear\DateTimeAdapter;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception as FinancialYearException;
use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Type;

class DateTimeAdapterTest extends BaseTestCase
{
    protected array $fyTypes = [Type::CALENDAR, Type::BUSINESS];

    /**
     * @throws \Exception
     */
    public function test_constructor_throws_exception_on_invalid_financial_year_type(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid Financial Year Type.');

        new DateTimeAdapter(
            fyType: 'invalid-type',
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );
    }

    /**
     * @throws \Exception
     */
    public function test_constructor_accepts_valid_string_and_converts_to_type_enum(): void
    {
        $calendar = new DateTimeAdapter(
            fyType: 'calendar',
            fyStartDate: $this->getRandomDateExcludingDisallowedFyCalendarTypeDates()
        );
        $business = new DateTimeAdapter(fyType: 'business', fyStartDate: $this->getRandomDateTime());

        $this->assertSame(Type::CALENDAR, $calendar->getType());
        $this->assertSame(Type::BUSINESS, $business->getType());
    }

    /**
     * @throws \Exception
     */
    public function test_financial_year_calendar_type_is_set_correctly(): void
    {
        $fy = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            fiftyThreeWeeks: false
        );

        $this->assertEquals(Type::CALENDAR, $fy->getType());
    }

    /**
     * @throws \Exception
     */
    public function test_financial_year_business_type_is_set_correctly(): void
    {
        $fy = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertEquals(Type::BUSINESS, $fy->getType());
    }

    /**
     * @throws \Exception
     */
    public function test_fy_weeks_returns_null_for_financial_year_calendar_type(): void
    {
        $fy = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            fiftyThreeWeeks: true
        );

        $this->assertNull($fy->getFyWeeks());
    }

    /**
     * Assert both true and false 53rd week scenarios.
     *
     * @throws \Exception
     */
    public function test_fy_weeks_returns_correct_weeks_for_financial_year_business_type(): void
    {
        $fy = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: true
        );

        $this->assertEquals(53, $fy->getFyWeeks());

        $fy = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: false
        );

        $this->assertEquals(52, $fy->getFyWeeks());
    }

    /**
     * @throws \Exception
     */
    public function test_fy_weeks_setter_throws_exception_for_financial_year_calendar_type(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Can not set the financial year weeks property for non business year type.');

        $fy = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
        );

        $fy->setFyWeeks(fiftyThreeWeeks: (bool) random_int(min: 0, max: 1));
    }

    /**
     * @throws \Exception
     */
    public function test_fy_periods_returns_correct_integer_for_calendar_type_financial_year(): void
    {
        $fy = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
        );

        // Calendar type has 12 periods
        $this->assertSame(12, $fy->getFyPeriods());
    }

    /**
     * @throws \Exception
     */
    public function test_fy_periods_returns_correct_integer_for_business_type_financial_year(): void
    {
        $fy = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: false
        );

        // Business type has 13 periods
        $this->assertSame(13, $fy->getFyPeriods());
    }

    /**
     * @throws \Exception
     */
    public function test_setting_same_fy_weeks_sets_weeks_without_changing_end_date_for_business_type(): void
    {
        $fiftyThreeWeeks = (bool) random_int(min: 0, max: 1);

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: $fiftyThreeWeeks
        );

        $fyEndDate = $dateTimeAdapter->getFyEndDate();

        $dateTimeAdapter->setFyWeeks(fiftyThreeWeeks: $fiftyThreeWeeks);

        $this->assertSame(
            $fyEndDate->format(format: 'YmdHis'),
            $dateTimeAdapter->getFyEndDate()->format(format: 'YmdHis')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_setting_different_fy_weeks_sets_weeks_with_different_end_date_for_business_type(): void
    {
        $fiftyThreeWeeks = (bool) random_int(min: 0, max: 1);

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: $this->getRandomDateTime(),
            fiftyThreeWeeks: $fiftyThreeWeeks
        );

        $fyEndDate = $dateTimeAdapter->getFyEndDate();

        // Set the opposite of original weeks.
        $dateTimeAdapter->setFyWeeks(fiftyThreeWeeks: !$fiftyThreeWeeks);

        $this->assertNotSame(
            $fyEndDate->format(format: 'YmdHis'),
            $dateTimeAdapter->getFyEndDate()->format(format: 'YmdHis')
        );
    }

    /**
     * Invalid dates are 29, 30, 31 of any month.
     *
     * @throws \Exception
     */
    public function test_set_fy_start_date_throws_exception_for_invalid_dates(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'This library does not support 29, 30, 31 as start dates of a month for calendar type financial year.'
        );

        $randomDateTime = $this->getRandomDateTime();

        $datesArray = [29, 30, 31];

        // Random Year, random disallowed date. Fix to May as we know it includes all 3 dates.
        new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: $randomDateTime->format(format: 'Y') . '-05-' . $datesArray[array_rand(array: $datesArray)],
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );
    }

    /**
     * @throws \Exception
     */
    public function test_set_fy_start_date_sets_new_fy_end_date_if_fy_start_date_is_different(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $type,
            fyStartDate: $type->isBusiness() ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $originalFyStartDate = $dateTimeAdapter->getFyStartDate();
        $originalFyEndDate = $dateTimeAdapter->getFyEndDate();

        $dateTimeAdapter->setFyStartDate(date:
            $type->isBusiness() ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates()
        );

        $this->assertNotSame(
            $originalFyStartDate->format(format: 'YmdHis'),
            $dateTimeAdapter->getFyStartDate()->format(format: 'YmdHis')
        );

        $this->assertNotSame(
            $originalFyEndDate->format(format: 'YmdHis'),
            $dateTimeAdapter->getFyEndDate()->format(format: 'YmdHis')
        );
    }

    /**
     * Assert DateTimeZone param is ignored if:
     * - fyStartDate param is a DateTime instance
     * - dateTimeZone param is provided.
     *
     * @throws \Exception
     */
    public function test_set_fy_start_date_ignores_date_time_zone_param_if_start_date_param_is_date_time_instance(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $defaultTimeZone = new DateTimeZone(timezone: 'UTC');
        $timeZone = new DateTimeZone(timezone: 'Europe/Athens');

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $type,
            fyStartDate: $type->isBusiness()
                ? $this->getRandomDateTime()->setTimezone(timezone: $defaultTimeZone)
                : $this->getRandomDateExcludingDisallowedFyCalendarTypeDates()->setTimezone(timezone: $defaultTimeZone),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1),
            dateTimeZone: $timeZone
        );

        $this->assertNotSame($timeZone->getName(), $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }

    /**
     * Assert Start Date timezone is set correctly if:
     * - fyStartDate param is a string
     * - dateTimeZone param is provided and is DateTimeZone instance.
     *
     * @throws \Exception
     */
    public function test_set_fy_start_date_sets_correct_time_zone_if_start_date_is_string_and_date_time_zone_instance(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $timeZone = new DateTimeZone(timezone: 'Europe/Athens');

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $type,
            fyStartDate: '2023-11-19',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1),
            dateTimeZone: $timeZone
        );

        $this->assertSame($timeZone->getName(), $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }

    /**
     * Assert Start Date timezone is set correctly if:
     * - fyStartDate param is a string
     * - dateTimeZone param is provided and is a string of available DateTimeZones.
     *
     * @throws \Exception
     */
    public function test_set_fy_start_date_sets_correct_time_zone_if_start_date_is_string_and_date_time_zone_string(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $timeZone = 'Europe/Athens';

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $type,
            fyStartDate: '2023-11-19',
            fiftyThreeWeeks: (bool) random_int(0, 1),
            dateTimeZone: $timeZone
        );

        $this->assertSame($timeZone, $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }

    /**
     * Assert an exception is thrown on setting FY Start Date if:
     * - dateTimeZone param is provided and is a string of available DateTimeZones.
     *
     * @throws \Exception
     */
    public function test_set_fy_start_date_throws_exception_if_invalid_date_time_zone_string_is_provided(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $timeZone = 'Random TimeZone';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid dateTimeZone string: ' . $timeZone);

        new DateTimeAdapter(
            fyType: $type,
            fyStartDate: '2023-11-19',
            fiftyThreeWeeks: (bool) random_int(0, 1),
            dateTimeZone: $timeZone
        );
    }

    /**
     * Assert an exception is thrown on setting FY Start Date if:
     * - dateTimeZone param is provided and is of an unsupported type.
     *
     * @throws \Exception
     */
    public function test_set_fy_start_date_throws_exception_if_invalid_date_time_zone_is_provided(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid dateTimeZone string: invalid-date-time-zone');

        new DateTimeAdapter(
            fyType: $type,
            fyStartDate: '2023-11-19',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1),
            dateTimeZone: 'invalid-date-time-zone'
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_period_by_id_returns_correct_time_period_for_calendar_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2nd Period should be 2019-02-01 - 2019-02-28
        $period = $dateTimeAdapter->getPeriodById(id: 2);

        $this->assertEquals('2019-02-01 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-02-28 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_period_by_id_returns_correct_time_period_for_calendar_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 1st Period should be 2019-01-01 - 2019-01-31
        $period = $dateTimeAdapter->getPeriodById(id: 1);

        $this->assertEquals('2019-01-01 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-01-31 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_period_by_id_returns_correct_time_period_for_calendar_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Last Period, 12th for calendar type, should be 2019-12-01 - 2019-12-31
        $period = $dateTimeAdapter->getPeriodById(id: 12);

        $this->assertEquals('2019-12-01 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-12-31 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_period_by_id_returns_correct_time_period_for_business_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2nd Period should be 2019-01-29 - 2019-02-26
        $period = $dateTimeAdapter->getPeriodById(id: 2);

        $this->assertEquals('2019-01-29 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-02-25 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_period_by_id_returns_correct_time_period_for_business_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 1st Period should be 2019-01-01 - 2019-01-28
        $period = $dateTimeAdapter->getPeriodById(id: 1);

        $this->assertEquals('2019-01-01 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-01-28 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_period_by_id_returns_correct_time_period_for_business_type_financial_year_fifty_two_weeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: false
        );

        // Last Period, 13th for business type, should be 2019-12-03 - 2019-12-30 for 52 weeks year.
        $period = $dateTimeAdapter->getPeriodById(id: 13);

        $this->assertEquals('2019-12-03 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-12-30 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_period_by_id_returns_correct_time_period_for_business_type_financial_year_fifty_three_weeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: true
        );

        // Last Period, 13th for business type, should be 2019-12-03 - 2020-01-06 for 53 weeks year.
        $period = $dateTimeAdapter->getPeriodById(id: 13);

        $this->assertEquals('2019-12-03 00:00:00', $period->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2020-01-06 00:00:00', $period->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_by_id_throws_exception_on_non_business_type_financial_year_type(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Week id is not applicable for non business type financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: true
        );

        $dateTimeAdapter->getBusinessWeekById(id: 1);
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_by_id_throws_exception_on_invalid_week_id(): void
    {
        $this->expectException(FinancialYearException::class);

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Build an array of integers of the financial year weeks.
        $fyWeeksArray = [];

        for ($i = 1; $i <= $dateTimeAdapter->getFyWeeks(); $i++) {
            $fyWeeksArray[] = $i;
        }

        // Get a random week id that's not equal to the available weeks.
        do {
            $randomWeekId = random_int(min: -1000, max: 1000);
        } while (in_array(needle: $randomWeekId, haystack: $fyWeeksArray, strict: true));

        // Set the expected message after we have set the financial year weeks
        $this->expectExceptionMessage('There is no week with id: ' . $randomWeekId . '.');

        $dateTimeAdapter->getBusinessWeekById(id: $randomWeekId);
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_by_id_method_returns_correct_week_period_for_business_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2nd week should be 2019-01-08 - 2019-01-14.
        $week = $dateTimeAdapter->getBusinessWeekById(id: 2);

        $this->assertEquals('2019-01-08 00:00:00', $week->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-01-14 00:00:00', $week->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_by_id_method_returns_correct_week_period_for_first_week_of_business_type_financial_year(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // First week should be 2019-01-01 - 2019-01-07.
        $firstWeek = $dateTimeAdapter->getBusinessWeekById(id: 1);

        $this->assertEquals('2019-01-01 00:00:00', $firstWeek->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-01-07 00:00:00', $firstWeek->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_by_id_method_returns_correct_week_period_for_last_week_of_business_type_financial_year_fifty_two_weeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: false
        );

        // Last week should be 2019-12-24 - 2019-12-30 for 52 weeks year.
        $lastWeek = $dateTimeAdapter->getBusinessWeekById(id: 52);

        $this->assertEquals('2019-12-24 00:00:00', $lastWeek->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2019-12-30 00:00:00', $lastWeek->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_by_id_method_returns_correct_week_period_for_last_week_of_business_type_financial_year_fifty_three_weeks(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: true
        );

        // Last week should be 2019-12-31 - 2020-01-06 for 53 weeks year.
        $lastWeek = $dateTimeAdapter->getBusinessWeekById(id: 53);

        $this->assertEquals('2019-12-31 00:00:00', $lastWeek->getStartDate()->format(format: 'Y-m-d H:i:s'));
        $this->assertEquals('2020-01-06 00:00:00', $lastWeek->getEndDate()->format(format: 'Y-m-d H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_period_id_by_date_throws_exception_on_date_before_financial_year(): void
    {
        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $this->fyTypes[array_rand(array: $this->fyTypes)],
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $dateTimeAdapter->getPeriodIdByDate(date: '2018-12-31');
    }

    /**
     * @throws \Exception
     */
    public function test_get_period_id_by_date_throws_exception_on_date_after_financial_year(): void
    {
        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $this->fyTypes[array_rand(array: $this->fyTypes)],
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2020-01-07 is out of range even if the type is business and weeks 53, if start date is 2019-01-01
        $dateTimeAdapter->getPeriodIdByDate(date: '2020-01-07');
    }

    /**
     * @throws \Exception
     */
    public function test_get_period_id_by_date_returns_correct_id_for_date(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $this->fyTypes[array_rand(array: $this->fyTypes)],
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2019-02-07 belongs to 2nd period for both types
        $this->assertEquals(2, $dateTimeAdapter->getPeriodIdByDate(date: '2019-02-07'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_id_by_date_throws_exception_on_non_business_type_financial_year(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Business weeks are set only for a business type financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $dateTimeAdapter->getBusinessWeekIdByDate(date: '2019-01-04');
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_id_by_date_throws_exception_on_date_before_financial_year(): void
    {
        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $dateTimeAdapter->getBusinessWeekIdByDate(date: '2018-12-31');
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_id_by_date_throws_exception_on_date_after_financial_year(): void
    {
        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage('The requested date is out of range of the current financial year.');

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2020-01-07 is out of range even if the type is business and weeks 53, if start date is 2019-01-01
        $dateTimeAdapter->getBusinessWeekIdByDate(date: '2020-01-07');
    }

    /**
     * @throws \Exception
     */
    public function test_get_business_week_id_by_date_returns_correct_id_for_date(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // 2019-01-31 belongs to 5th week
        $this->assertEquals(5, $dateTimeAdapter->getBusinessWeekIdByDate(date: '2019-01-31'));
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_date_of_period_by_id_returns_financial_year_start_date_for_first_period(): void
    {
        $type = $this->fyTypes[array_rand($this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $type,
            fyStartDate: $type->isBusiness() ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertSame($dateTimeAdapter->getFyStartDate(), $dateTimeAdapter->getFirstDateOfPeriodById(id: 1));
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_date_of_period_by_id_returns_correct_date_for_calendar_type(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertEquals(
            '2019-04-01 00:00:00',
            $dateTimeAdapter->getFirstDateOfPeriodById(id: 4)->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_date_of_period_by_id_returns_correct_date_for_business_type(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertEquals(
            '2019-12-03 00:00:00',
            $dateTimeAdapter->getFirstDateOfPeriodById(id: 13)->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_date_of_period_by_id_returns_financial_year_end_date_for_last_period(): void
    {
        $type = $this->fyTypes[array_rand(array: $this->fyTypes)];

        $dateTimeAdapter = new DateTimeAdapter(
            fyType: $type,
            fyStartDate: $type->isBusiness() ?
                $this->getRandomDateTime() :
                $this->getRandomDateExcludingDisallowedFyCalendarTypeDates(),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertSame(
            $dateTimeAdapter->getFyEndDate(),
            $dateTimeAdapter->getLastDateOfPeriodById(id: $dateTimeAdapter->getFyPeriods())
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_date_of_period_by_id_returns_correct_date_for_calendar_type(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::CALENDAR,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertEquals(
            '2019-04-30 00:00:00',
            $dateTimeAdapter->getLastDateOfPeriodById(id: 4)->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_date_of_period_by_id_returns_correct_date_for_business_type(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertEquals(
            '2019-12-02 00:00:00',
            $dateTimeAdapter->getLastDateOfPeriodById(id: 12)->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_date_of_business_week_by_id_method_for_first_week_returns_financial_year_start_date(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        $this->assertSame($dateTimeAdapter->getFyStartDate(), $dateTimeAdapter->getFirstDateOfBusinessWeekById(id: 1));
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_date_of_business_week_by_id_returns_correct_date(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Test start of week 49 (start of period 13).
        // Expect 2019-12-03.
        $this->assertEquals(
            '2019-12-03 00:00:00',
            $dateTimeAdapter->getFirstDateOfBusinessWeekById(id: 49)->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_date_of_business_week_by_id_method_for_last_week_returns_financial_year_end_date(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Use the weeks that are already set in the adapter.
        $this->assertSame(
            $dateTimeAdapter->getFyEndDate(),
            $dateTimeAdapter->getLastDateOfBusinessWeekById(id: $dateTimeAdapter->getFyWeeks())
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_last_date_of_business_week_by_id_returns_correct_date(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Test end of week 49.
        // Expect 2019-12-09.
        $this->assertEquals(
            '2019-12-09 00:00:00',
            $dateTimeAdapter->getLastDateOfBusinessWeekById(id: 49)->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_first_business_week_by_period_id_returns_correct_week(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Test first week of period 13. That is week 49. 2019-12-03 - 2019-12-09.
        $firstBusinessWeekOfPeriod = $dateTimeAdapter->getFirstBusinessWeekByPeriodId(id: 13);

        $this->assertEquals(
            '2019-12-03 00:00:00',
            $firstBusinessWeekOfPeriod->getStartDate()->format(format: 'Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-12-09 00:00:00',
            $firstBusinessWeekOfPeriod->getEndDate()->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_second_business_week_by_period_id_returns_correct_week(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Test second week of period 12. That is week 46. 2019-11-12 - 2019-11-18.
        $secondBusinessWeekOfPeriod = $dateTimeAdapter->getSecondBusinessWeekByPeriodId(id: 12);

        $this->assertEquals(
            '2019-11-12 00:00:00',
            $secondBusinessWeekOfPeriod->getStartDate()->format(format: 'Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-11-18 00:00:00',
            $secondBusinessWeekOfPeriod->getEndDate()->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_third_business_week_by_period_id_returns_correct_week(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Test third week of period 11. That is week 43. 2019-10-22 - 2019-10-28.
        $thirdBusinessWeekOfPeriod = $dateTimeAdapter->getThirdBusinessWeekOfPeriodId(id: 11);

        $this->assertEquals(
            '2019-10-22 00:00:00',
            $thirdBusinessWeekOfPeriod->getStartDate()->format(format: 'Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-10-28 00:00:00',
            $thirdBusinessWeekOfPeriod->getEndDate()->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_fourth_business_week_by_period_id_returns_correct_week(): void
    {
        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );

        // Test fourth week of period 11. That is week 44. 2019-10-29 - 2019-11-04.
        $fourthBusinessWeekOfPeriod = $dateTimeAdapter->getFourthBusinessWeekByPeriodId(id: 11);

        $this->assertEquals(
            '2019-10-29 00:00:00',
            $fourthBusinessWeekOfPeriod->getStartDate()->format(format: 'Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2019-11-04 00:00:00',
            $fourthBusinessWeekOfPeriod->getEndDate()->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws FinancialYearException
     */
    public function test_get_fifty_third_business_week_by_period_id_returns_correct_week(): void
    {
        // Week 53 is only available for the relevant year and is the last week of the year.

        // Financial Year starts at 2019-01-01
        $dateTimeAdapter = new DateTimeAdapter(
            fyType: Type::BUSINESS,
            fyStartDate: '2019-01-01',
            fiftyThreeWeeks: true
        );

        // Expect fifty third week range: 2019-12-31 - 2020-01-06
        $fiftyThreeWeek = $dateTimeAdapter->getFiftyThirdBusinessWeek();

        $this->assertEquals(
            '2019-12-31 00:00:00',
            $fiftyThreeWeek->getStartDate()->format(format: 'Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2020-01-06 00:00:00',
            $fiftyThreeWeek->getEndDate()->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws \Exception
     */
    public function test_get_date_object_throws_exception_for_invalid_string(): void
    {
        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage(
            'Invalid date format. Not a valid ISO-8601 date string or DateTime/DateTimeImmutable object.'
        );

        new DateTimeAdapter(
            fyType: $this->fyTypes[array_rand(array: $this->fyTypes)],
            fyStartDate: bin2hex(string: random_bytes(length: 20)),
            fiftyThreeWeeks: (bool) random_int(min: 0, max: 1)
        );
    }

    /**
     * @throws \Exception
     */
    public function test_exception_on_invalid_period_for_calendar_type_financial_year(): void
    {
        $startDate = new DateTime(datetime: '2019-01-01');

        $fy = new DateTimeAdapter(fyType: 'calendar', fyStartDate: $startDate);

        $fyPeriodsArray = [];

        for ($i = 1; $i <= $fy->getFyPeriods(); $i++) {
            $fyPeriodsArray[] = $i;
        }

        do {
            $randomPeriodId = random_int(min: -1000, max: 1000);
        } while (in_array(needle: $randomPeriodId, haystack: $fyPeriodsArray, strict: true));

        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage('There is no period with id: ' . $randomPeriodId);

        // A Calendar Type Financial Year has 12 periods only.
        $fy->getPeriodById(id: $randomPeriodId);
    }

    /**
     * @throws \Exception
     */
    public function test_exception_on_invalid_period_id_for_business_type_financial_year(): void
    {
        $startDate = new DateTime(datetime: '2019-01-01');

        $fy = new DateTimeAdapter(fyType: 'business', fyStartDate: $startDate);

        $fyPeriodsArray = [];

        for ($i = 1; $i <= $fy->getFyPeriods(); $i++) {
            $fyPeriodsArray[] = $i;
        }

        do {
            $randomPeriodId = random_int(min: -1000, max: 1000);
        } while (in_array(needle: $randomPeriodId, haystack: $fyPeriodsArray, strict: true));

        $this->expectException(FinancialYearException::class);
        $this->expectExceptionMessage('There is no period with id: ' . $randomPeriodId . '.');

        // A Calendar Type Financial Year has 12 periods only.
        $fy->getPeriodById(id: $randomPeriodId);
    }

    /**
     * Generate a random date excluding the ones disallowed for calendar type financial year.
     *
     * The generated date string is valid formatted so bool (false) would never be returned.
     *
     * @throws \Exception
     */
    private function getRandomDateExcludingDisallowedFyCalendarTypeDates(): DateTimeImmutable
    {
        $randomDateTime = $this->getRandomDateTime();

        // Get a random date string with date (day) number that does not include the disallowed dates (29, 30, 31)
        $randomDateString = sprintf('%s%s%s%s%s',
            $randomDateTime->format(format: 'Y'),
            '-',
            $randomDateTime->format(format: 'm'),
            '-',
            (string) random_int(min: 1, max: 28)
        );

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
