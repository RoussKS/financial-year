<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear\Tests\Unit;

use DateTimeZone;
use RoussKS\FinancialYear\DateTimeAdapterFactory;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Type;

class DateTimeAdapterFactoryTest extends BaseTestCase
{
    /**
     * @throws ConfigException
     * @throws Exception
     */
    public function test_create_returns_date_time_adapter_with_provided_configuration(): void
    {
        $dateTimeAdapter = DateTimeAdapterFactory::create(
            fyType: Type::BUSINESS,
            fyStartDate: '2023-01-01',
            fiftyThreeWeeks: true
        );

        $this->assertSame(Type::BUSINESS, $dateTimeAdapter->getType());
        $this->assertSame(53, $dateTimeAdapter->getFyWeeks());
        $this->assertSame(
            '2023-01-01 00:00:00',
            $dateTimeAdapter->getFyStartDate()->format(format: 'Y-m-d H:i:s')
        );
    }

    /**
     * @throws ConfigException
     * @throws Exception
     */
    public function test_create_passes_date_time_zone_for_string_start_dates(): void
    {
        $timeZone = new DateTimeZone(timezone: 'Europe/Athens');

        $dateTimeAdapter = DateTimeAdapterFactory::create(
            fyType: Type::CALENDAR,
            fyStartDate: '2023-11-19',
            dateTimeZone: $timeZone
        );

        $this->assertSame($timeZone->getName(), $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }
}
