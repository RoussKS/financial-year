<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @throws \Exception
     */
    protected function getRandomDateTime(): DateTimeImmutable
    {
        return (new DateTimeImmutable(datetime: 'now'))->setTimestamp(timestamp: random_int(min: 1, max: 2147385600));
    }
}
