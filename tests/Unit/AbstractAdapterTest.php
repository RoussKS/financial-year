<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use RoussKS\FinancialYear\AbstractAdapter;
use RoussKS\FinancialYear\AdapterInterface;
use RoussKS\FinancialYear\Tests\BaseTestCase;
use RoussKS\FinancialYear\Exceptions\ConfigException;

/**
 * Class AbstractAdapterTest
 *
 * @package RoussKS\FinancialYear\Tests\Unit
 */
class AbstractAdapterTest extends BaseTestCase
{
    /**
     * @test
     *
     * @return void
     * @throws \Exception
     */
    public function assertConstructorThrowsExceptionOnInvalidFinancialYearType(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid Financial Year Type.');

        $this->getMockForAbstractClass(AbstractAdapter::class, [
            'test',
            (bool) random_int(0, 1)
        ]);
    }

    /**
     * @test
     *
     * @return void
     * @throws \Exception
     */
    public function assertGetTypeReturnsString(): void
    {
        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar',
            (bool) random_int(0, 1)
        ]);

        $this->assertIsString($fy->getType());
    }

    /**
     * @test
     *
     * @return void
     * @throws \Exception
     */
    public function assertFinancialYearCalendarType(): void
    {
        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar',
            (bool) random_int(0, 1)
        ]);

        $this->assertEquals(AbstractAdapter::TYPE_CALENDAR, $fy->getType());
    }

    /**
     * @test
     *
     * @return void
     * @throws \Exception
     */
    public function assertFinancialYearBusinessType(): void
    {
        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business',
            (bool) random_int(0, 1)
        ]);

        $this->assertEquals(AbstractAdapter::TYPE_BUSINESS, $fy->getType());
    }

    /**
     * @test
     *
     * @return void
     * @throws \Exception
     */
    public function assertFyWeeksReturnsNullForFinancialYearCalendarType(): void
    {
        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar',
            (bool) random_int(0, 1)
        ]);

        $this->assertNull($fy->getFyWeeks());
    }

    /**
     * @test
     *
     * Assert both true and false scenarios.
     *
     * @return void
     */
    public function assertFyWeeksReturnsIntForFinancialYearBusinessType(): void
    {
        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', true
        ]);

        $this->assertIsInt($fy->getFyWeeks());

        /** @var AdapterInterface $fy */
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
     */
    public function assertFyWeeksReturnsCorrectWeeksForFinancialYearBusinessType(): void
    {
        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'business', true
        ]);

        $this->assertEquals(53, $fy->getFyWeeks());

        /** @var AdapterInterface $fy */
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
     */
    public function assertFyWeeksSetterThrowsExceptionForFinancialYearCalendarType(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Can not set the financial year weeks property for non business year type.');

        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', true
        ]);

        $fy->setFyWeeks((bool) random_int(0, 1));
    }

    /**
     * @test
     *
     * @return void
     */
    public function assertFyPeriodsReturnsCorrectIntegerForCalendarTypeFinancialYear(): void
    {
        /** @var AdapterInterface $fy */
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
     */
    public function assertFyPeriodsReturnsCorrectIntegerForBusinessTypeFinancialYear(): void
    {
        /** @var AdapterInterface $fy */
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
     */
    public function assertValidationThrowsExceptionForMissingDates(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Invalid configuration of financial year adapter.');

        /** @var AdapterInterface $fy */
        $fy = $this->getMockForAbstractClass(AbstractAdapter::class, [
            'calendar', true
        ]);

        $fy->validateConfiguration();
    }
}
