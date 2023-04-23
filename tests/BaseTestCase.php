<?php

namespace RoussKS\FinancialYear\Tests;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @return \DateTimeImmutable|\DateTimeInterface
     * @throws \Exception
     */
    protected function getRandomDateTime(): DateTimeInterface
    {
        return (new DateTimeImmutable('now'))->setTimestamp(random_int(1, 2147385600));
    }
}
