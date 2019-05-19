<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Tests\MockObjects\MockDateTimeInterfaceClass;

class FinancialYearTest extends BaseTestCase
{
    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionOnClassInstantiationWithoutConfig(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\Exception::class);
        $this->expectExceptionMessage('The adapter has not been set yet');

        $fy = new \RoussKS\FinancialYear\FinancialYear($this->faker->dateTime());

        $fy->getAdapter();
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfReInstantiatedFromMethod(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\Exception::class);
        $this->expectExceptionMessage('The adapter has already been instantiated');

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new \RoussKS\FinancialYear\FinancialYear($dateTime, $config);

        $fy->instantiateFinancialYearAdapter($config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterInstantiationMethodThrowsExceptionOnUnsupportedAdapterType(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\ConfigException::class);

        $fakeDateTimeInterfaceClass = new MockDateTimeInterfaceClass();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $fakeDateTimeInterfaceClass,
        ];

        new \RoussKS\FinancialYear\FinancialYear($fakeDateTimeInterfaceClass, $config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \Exception
     */
    public function assertAdapterNotNullIfInstantiatedOnMethod(): void
    {
        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new \RoussKS\FinancialYear\FinancialYear($dateTime);


        $fy->instantiateFinancialYearAdapter($config);

        // The return object is type-hinted, so no need to test that it is Financial Year Adapter Interface.
        $this->assertNotNull($fy->getAdapter());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfTypeIsNotSet(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\ConfigException::class);

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyStartDate' => $dateTime,
        ];

        new \RoussKS\FinancialYear\FinancialYear($dateTime, $config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfTypeIsNotString(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\ConfigException::class);

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => [],
            'fyStartDate' => $dateTime,
        ];

        new \RoussKS\FinancialYear\FinancialYear($dateTime, $config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfStartDateIsNotSet(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\ConfigException::class);

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
        ];

        new \RoussKS\FinancialYear\FinancialYear($dateTime, $config);
    }
}