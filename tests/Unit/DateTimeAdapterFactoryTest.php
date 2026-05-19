<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use DateTimeZone;
use RoussKS\FinancialYear\AbstractAdapter;
use RoussKS\FinancialYear\DateTimeAdapter;
use RoussKS\FinancialYear\DateTimeAdapterFactory;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Tests\BaseTestCase;

class DateTimeAdapterFactoryTest extends BaseTestCase
{
    /**
     * @test
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertCreateReturnsDateTimeAdapterWithProvidedConfiguration(): void
    {
        $dateTimeAdapter = DateTimeAdapterFactory::create(
            AbstractAdapter::TYPE_BUSINESS,
            '2023-01-01',
            true
        );

        $this->assertInstanceOf(DateTimeAdapter::class, $dateTimeAdapter);
        $this->assertSame(AbstractAdapter::TYPE_BUSINESS, $dateTimeAdapter->getType());
        $this->assertSame(53, $dateTimeAdapter->getFyWeeks());
        $this->assertSame('2023-01-01 00:00:00', $dateTimeAdapter->getFyStartDate()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertCreatePassesDateTimeZoneForStringStartDates(): void
    {
        $timeZone = new DateTimeZone('Europe/Athens');

        $dateTimeAdapter = DateTimeAdapterFactory::create(
            AbstractAdapter::TYPE_CALENDAR,
            '2023-11-19',
            false,
            $timeZone
        );

        $this->assertSame($timeZone->getName(), $dateTimeAdapter->getFyStartDate()->getTimezone()->getName());
    }
}
