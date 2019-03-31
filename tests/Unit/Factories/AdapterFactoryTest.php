<?php

namespace RoussKS\FinancialYear\Tests\Unit\Factories;

use PHPUnit\Framework\TestCase;
use RoussKS\FinancialYear\Tests\MockObjects\MockDateTimeInterfaceClass;

class AdapterFactoryTest extends TestCase
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
}