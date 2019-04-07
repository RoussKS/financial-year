<?php

namespace RoussKS\FinancialYear\Tests\Unit\Enums;

use RoussKS\FinancialYear\Tests\BaseTestCase;

class TypeEnumTest extends BaseTestCase
{
    /**
     * @test
     */
    public function assertTypeIsNotValidCalendarType(): void
    {
        $result = \RoussKS\FinancialYear\Enums\TypeEnum::isCalendar('any-text');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function assertTypeIsNotValidBusinessTypeType(): void
    {
        $result = \RoussKS\FinancialYear\Enums\TypeEnum::isBusiness('any-text');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function assertTypeIsValidCalendarType()
    {
        $result = \RoussKS\FinancialYear\Enums\TypeEnum::isCalendar('calendar');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function assertTypeIsValidBusinessType()
    {
        $result = \RoussKS\FinancialYear\Enums\TypeEnum::isBusiness('business');

        $this->assertTrue($result);
    }
}