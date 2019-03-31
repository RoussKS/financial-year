<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use PHPUnit\Framework\TestCase;

class FinancialYearTest extends TestCase
{
    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterInterfaceReturnedOnConstructWithConfig(): void
    {
        $dateTime = new \DateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new \RoussKS\FinancialYear\FinancialYear($dateTime, $config);

        $this->assertNotNull($fy->getFinancialYearAdapter());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfNotInstantiated(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\Exception::class);

        $fy = new \RoussKS\FinancialYear\FinancialYear(new \DateTime());

        $fy->getFinancialYearAdapter();
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfReInstantiated(): void
    {
        $this->expectException(\RoussKS\FinancialYear\Exceptions\Exception::class);

        $dateTime = new \DateTime();

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
     * @throws \Exception
     */
    public function assertAdapterNotNullIfInstantiatedOnMethod(): void
    {
        $dateTime = new \DateTime();

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new \RoussKS\FinancialYear\FinancialYear($dateTime);


        $fy->instantiateFinancialYearAdapter($config);

        $this->assertNotNull($fy->getFinancialYearAdapter());
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

        $dateTime = new \DateTime();

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

        $dateTime = new \DateTime();

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

        $dateTime = new \DateTime();

        $config = [
            'fyType' => 'calendar',
        ];

        new \RoussKS\FinancialYear\FinancialYear($dateTime, $config);
    }
}