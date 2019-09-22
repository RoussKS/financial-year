<?php

namespace RoussKS\FinancialYear\Tests\Unit\Adapters;

use ReflectionException;
use RoussKS\FinancialYear\AbstractAdapter;
use RoussKS\FinancialYear\AdapterInterface;
use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Exceptions\ConfigException;

/**
 * Class AbstractAdapterTest
 *
 * @package RoussKS\FinancialYear\Tests\Unit\Adapters
 */
class AbstractAdapterTest extends BaseTestCase
{
    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertConstructorThrowsExceptionOnInvalidFinancialYearType(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid Financial Year Type');

        $this->getMockForAbstractClass(AbstractAdapter::class, [
            'test', $this->faker->boolean
        ]);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertGetTypeReturnsString(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', $this->faker->boolean
        ]);

        $this->assertIsString($fy->getType());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFinancialYearCalendarType(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', $this->faker->boolean
        ]);

        $this->assertEquals(AbstractAdapter::TYPE_CALENDAR, $fy->getType());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFinancialYearBusinessType(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', $this->faker->boolean
        ]);

        $this->assertEquals(AbstractAdapter::TYPE_BUSINESS, $fy->getType());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFyWeeksReturnsNullForFinancialYearCalendarType(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', $this->faker->boolean
        ]);

        $this->assertNull($fy->getFyWeeks());
    }

    /**
     * @test
     *
     * Assert both true and false scenarios.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFyWeeksReturnsIntForFinancialYearBusinessType(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', true
        ]);

        $this->assertIsInt($fy->getFyWeeks());

        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', false
        ]);

        $this->assertIsInt($fy->getFyWeeks());
    }

    /**
     * @test
     *
     * Assert both true (53 weeks) and false (52 weeks) scenarios.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFyWeeksReturnsCorrectWeeksForFinancialYearBusinessType(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', true
        ]);

        $this->assertEquals(53, $fy->getFyWeeks());

        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', false
        ]);

        $this->assertEquals(52, $fy->getFyWeeks());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws ReflectionException
     */
    public function assertFyWeeksSetterThrowsExceptionForFinancialYearCalendarType(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Can not set the financial year weeks property for non business year type');

        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', true
        ]);

        $fy->setFyWeeks($this->faker->boolean);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFyPeriodsReturnsCorrectIntegerForCalendarTypeFinancialYear(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', true
        ]);

        // Calendar type has 12 periods
        $this->assertSame(12, $fy->getFyPeriods());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function assertFyPeriodsReturnsCorrectIntegerForBusinessTypeFinancialYear(): void
    {
        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', true
        ]);

        // Business type has 13 periods
        $this->assertSame(13, $fy->getFyPeriods());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws ConfigException
     * @throws ReflectionException
     */
    public function assertValidationThrowsExceptionForMissingDates(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid configuration of financial year adapter');

        /** @var  $fy AdapterInterface */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', true
        ]);

        $fy->validateConfiguration();
    }
}
