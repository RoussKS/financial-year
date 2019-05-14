<?php

namespace RoussKS\FinancialYear\Tests\Unit\Adapters;

use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Tests\MockObjects\MockDateTimeInterfaceClass;

class AdapterFactoryTest extends BaseTestCase
{
    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterFactoryThrowsExceptionOnUnsupportedAdapterType(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\ConfigException::class);

        $fakeDateTimeInterfaceClass = new MockDateTimeInterfaceClass();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $fakeDateTimeInterfaceClass,
        ];

        \RoussKS\FinancialYear\Adapters\AdapterFactory::createAdapter($fakeDateTimeInterfaceClass, $config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     */
    public function assertAdapterFactoryReturnsDateTimeAdapterWithCorrectConfig()
    {
        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $dateTimeAdapter = \RoussKS\FinancialYear\Adapters\AdapterFactory::createAdapter($dateTime, $config);

        $this->assertInstanceOf(\RoussKS\FinancialYear\Adapters\DateTimeAdapter::class, $dateTimeAdapter);
    }
}