<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\FinancialYear;

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
        $dateTime = new \DateTime('now');

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new FinancialYear($dateTime, $config);

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
        $this->expectException(Exception::class);

        $fy = new FinancialYear(new \DateTime('now'));

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
        $this->expectException(Exception::class);

        $dateTime = new \DateTime('now');

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
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \Exception
     */
    public function assertAdapterNotNullIfInstantiatedOnMethod(): void
    {
        $dateTime = new \DateTime('now');

        $config = [
            'fyType' => 'calendar',
            'fyStartDate' => $dateTime,
        ];

        $fy = new FinancialYear($dateTime);


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
        $this->expectException(ConfigException::class);

        $dateTime = new \DateTime('now');

        $config = [
            'fyStartDate' => $dateTime,
        ];

        new FinancialYear($dateTime, $config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfTypeIsNotString(): void
    {
        $this->expectException(ConfigException::class);

        $dateTime = new \DateTime('now');

        $config = [
            'fyType' => [],
            'fyStartDate' => $dateTime,
        ];

        new FinancialYear($dateTime, $config);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfStartDateIsNotSet(): void
    {
        $this->expectException(ConfigException::class);

        $dateTime = new \DateTime('now');

        $config = [
            'fyType' => 'calendar',
        ];

        new FinancialYear($dateTime, $config);
    }
}