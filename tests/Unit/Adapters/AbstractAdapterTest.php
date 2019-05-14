<?php

namespace RoussKS\FinancialYear\Tests\Unit\Adapters;

use RoussKS\FinancialYear\Adapters\AbstractAdapter;
use RoussKS\FinancialYear\Tests\BaseTestCase;

class AbstractAdapterTest extends BaseTestCase
{
    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertConstructorThrowsExceptionOnInvalidFinancialYearType(): void
    {
        $this->expectException('RoussKS\FinancialYear\Exceptions\ConfigException');
        $this->expectExceptionMessage('Invalid Financial Year Type');

        $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'test', $this->faker->boolean
        ]);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertGetTypeReturnsString(): void
    {
        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'calendar', $this->faker->boolean
        ]);

        $this->assertIsString($fy->getType());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertFinancialYearCalendarType(): void
    {
        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'calendar', $this->faker->boolean
        ]);

        $this->assertEquals(AbstractAdapter::TYPE_CALENDAR, $fy->getType());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertFinancialYearBusinessType(): void
    {
        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'business', $this->faker->boolean
        ]);

        $this->assertEquals(AbstractAdapter::TYPE_BUSINESS, $fy->getType());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertFyWeeksReturnsNullForFinancialYearCalendarType(): void
    {
        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'calendar', $this->faker->boolean
        ]);

        $this->assertNull($fy->getFyWeeks());
    }

    /**
     * @test
     *
     * Assert both true and false scenarios.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertFyWeeksReturnsIntForFinancialYearBusinessType(): void
    {
        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'business', true
        ]);

        $this->assertIsInt($fy->getFyWeeks());

        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'business', false
        ]);

        $this->assertIsInt($fy->getFyWeeks());
    }

    /**
     * @test
     *
     * Assert both true (53 weeks) and false (52 weeks) scenarios.
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertFyWeeksReturnsCorrectWeeksForFinancialYearBusinessType(): void
    {
        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'business', true
        ]);

        $this->assertEquals(53, $fy->getFyWeeks());

        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'business', false
        ]);

        $this->assertEquals(52, $fy->getFyWeeks());
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertFyWeeksSetterThrowsExceptionForFinancialYearCalendarType(): void
    {
        $this->expectException('RoussKS\FinancialYear\Exceptions\ConfigException');
        $this->expectExceptionMessage('Can not set the financial year weeks property for non business year type');

        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'calendar', true
        ]);

        $fy->setFyWeeks($this->faker->boolean);
    }

    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertValidationThrowsExceptionForMissingDates(): void
    {
        $this->expectException('RoussKS\FinancialYear\Exceptions\ConfigException');
        $this->expectExceptionMessage('Invalid configuration of financial year adapter');

        /** @var  $fy \RoussKS\FinancialYear\Adapters\AdapterInterface */
        $fy = $this->getMockForAbstractClass('RoussKS\FinancialYear\Adapters\AbstractAdapter', [
            'calendar', true
        ]);

        $fy->validate();
    }
}