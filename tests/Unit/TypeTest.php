<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RoussKS\FinancialYear\Type;

class TypeTest extends TestCase
{
    public function test_type_is_created_from_valid_string_values(): void
    {
        $this->assertSame(Type::CALENDAR, Type::from(value: 'calendar'));
        $this->assertSame(Type::BUSINESS, Type::from(value: 'business'));
    }

    public function test_type_helper_methods_return_expected_results(): void
    {
        $this->assertTrue(Type::CALENDAR->isCalendar());
        $this->assertFalse(Type::CALENDAR->isBusiness());
        $this->assertTrue(Type::CALENDAR->isNotBusiness());

        $this->assertTrue(Type::BUSINESS->isBusiness());
        $this->assertFalse(Type::BUSINESS->isCalendar());
        $this->assertFalse(Type::BUSINESS->isNotBusiness());
    }
}
