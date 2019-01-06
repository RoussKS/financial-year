<?php

namespace RoussKS\FinancialYear\Tests\Unit;

use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\FinancialYear;
use RoussKS\FinancialYear\Tests\BaseTestCase;

class FinancialYearTest extends BaseTestCase
{
    /**
     * @test
     *
     * @throws \RoussKS\FinancialYear\Exceptions\Exception
     * @throws \ReflectionException
     * @throws \RoussKS\FinancialYear\Exceptions\ConfigException
     * @throws \Exception
     */
    public function assertAdapterThrowsExceptionIfNotInstantiated()
    {
        $this->expectException(Exception::class);

        $fy = new FinancialYear(new \DateTime('now'));

        $fy->getFinancialYearAdapter();
    }
}