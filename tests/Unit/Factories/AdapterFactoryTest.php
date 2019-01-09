<?php

namespace RoussKS\FinancialYear\Tests\Unit\Factories;

use PHPUnit\Framework\TestCase;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Factories\AdapterFactory;
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
        $this->expectException(ConfigException::class);

        $fakeDateTimeInterfaceClass = new MockDateTimeInterfaceClass();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $fakeDateTimeInterfaceClass,
        ];

        AdapterFactory::createAdapter($fakeDateTimeInterfaceClass, $config);
    }
}