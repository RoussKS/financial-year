<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear\Tests;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @throws \Exception
     */
    protected function getRandomDateTime(): DateTimeImmutable
    {
        return (new DateTimeImmutable('now'))->setTimestamp(random_int(1, 2147385600));
    }
}
