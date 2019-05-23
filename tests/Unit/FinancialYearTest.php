<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\FinancialYear;
use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Tests\MockObjects\MockDateTimeInterfaceClass;

class FinancialYearTest extends BaseTestCase
{
    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterThrowsExceptionOnClassInstantiationWithoutConfig(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The adapter has not been set yet');

        $fy = new FinancialYear($this->faker->dateTime());

        $fy->getAdapter();
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterThrowsExceptionIfReInstantiatedFromMethod(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The adapter has already been instantiated');

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new FinancialYear($dateTime, $config);

        $fy->instantiateFinancialYearAdapter($config);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterInstantiationMethodThrowsExceptionOnUnsupportedAdapterType(): void
    {
        $this->expectException(ConfigException::class);

        $fakeDateTimeInterfaceClass = new MockDateTimeInterfaceClass();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $fakeDateTimeInterfaceClass,
        ];

        new FinancialYear($fakeDateTimeInterfaceClass, $config);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterNotNullIfInstantiatedOnMethod(): void
    {
        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new FinancialYear($dateTime);


        $fy->instantiateFinancialYearAdapter($config);

        // The return object is type-hinted, so no need to test that it is Financial Year Adapter Interface.
        $this->assertNotNull($fy->getAdapter());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterThrowsExceptionIfTypeIsNotSet(): void
    {
        $this->expectException(ConfigException::class);

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyStartDate' => $dateTime,
        ];

        new FinancialYear($dateTime, $config);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterThrowsExceptionIfTypeIsNotString(): void
    {
        $this->expectException(ConfigException::class);

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => [],
            'fyStartDate' => $dateTime,
        ];

        new FinancialYear($dateTime, $config);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws Exception
     */
    public function assertAdapterThrowsExceptionIfStartDateIsNotSet(): void
    {
        $this->expectException(ConfigException::class);

        $dateTime = $this->faker->dateTime();

        $config = [
            'fyType' => 'calendar',
        ];

        new FinancialYear($dateTime, $config);
    }
}