# \RoussKS\FinancialYear - Financial Year PHP Library

[![Latest Version](https://img.shields.io/github/release/RoussKS/financial-year.svg?style=round-square)](https://github.com/RoussKS/financial-year/releases)
![Build Status](https://github.com/RoussKS/financial-year/actions/workflows/test.yml/badge.svg)
[![GitHub license](https://img.shields.io/github/license/RoussKS/financial-year.svg)](https://github.com/RoussKS/financial-year/blob/master/LICENSE)

### Introduction / Background / Purpose
In my first working environment as a software/web developer, I stumbled upon the need to do different calculations for financial/accounting year purposes.
The business also happened to have a non-standard calendar financial year (types explained below).
Hence, each time we needed to get any sort of report (transactions, conversion rate, sales etc) we used a predefined list provided by our business analyst. 

This library aims to solve this business problem in a consistent manner.

The calculation of week, period, year and end dates of those for the business.

According to [wikipedia](https://en.wikipedia.org/wiki/Fiscal_year)

>A fiscal year (or financial year, or sometimes budget year) is the period used by governments for accounting and budget purposes, which varies between countries. It is also used for financial reporting by business and other organizations.

An organisation financial year can be based on the following 2 methods:
1. Starting on the same date every year.
   - This is the *`calendar`* type for this library.
2. Ending on the same day of the week every year.
   - This is the *`business`* type for this library.
     >The "fiscal year's end" (FYE) is the date that marks the end of the fiscal year. Some companies—such as Cisco Systems[1]—end their fiscal year on the same day of the week each year, e.g. the day that is closest to a particular date (for example, the Friday closest to 31 December). Under such a system, some fiscal years will have 52 weeks and others 53 weeks.
   - A financial year of this type, always has 364 days a year and is divided in 13 periods (each period has 4 weeks, 28 days).
   - The current library will accommodate the 52-53 weeks methodology of financial year, 
     where a 53rd week is added at the end of a financial year in order to cover for missing days from the previous 364 day years.
     This is a business domain decision, hence the library does not calculate when a year should be 52 or 53 weeks.
     It needs to be set by the user.
     
     
Available methods can be followed through the [RoussKS\FinancialYear\AdapterInterface](https://github.com/RoussKS/financial-year/blob/master/src/AdapterInterface.php) until a full-fledged readme is provided.

### Requirements
- PHP Version ^7.1 || ^8.0 ( 7.1 =< PHP Version =< 8.x.x according to [Composer docs version constraints](https://getcomposer.org/doc/articles/versions.md#caret-version-range-) )

### Installation
```console
composer require roussks/financial-year
```

### Basic Use
```php
require_once __DIR__ . '/vendor/autoload.php';

// DateTimeAdapter
// If instantiating with string, it must be of ISO-8601 format 'YYYY-MM-DD'
$startDate = new \DateTime('2019-01-01');

$fy = new \RoussKS\FinancialYear\DateTimeAdapter('calendar', $startDate);

echo $fy->getFyEndDate()->format('Y-m-d'); // 2019-12-31 
```

### Docker images
The library provides a sample Dockerfile to assist in development use if you want to contribute.
This using the official php cli images.
Copy the `Dockerfile.example` file to Dockerfile, uncommenting the required php version.

However, you are free to use any methodology you want for developing updates & bugfixes.

### Run Tests
The library has an extensive test suite to cover most scenarios & negative paths.

Be aware that some configurations for phpunit are different between versions, 
so you might need to copy the `phpunit.xml.dist` file to `phpunit.xml` and make the necessary adjustments.

```shell
./vendor/phpunit/phpunit/phpunit
```

```shell
XDEBUG_MODE=coverage ./vendor/phpunit/phpunit/phpunit
```


```shell
# HTML coverage example
XDEBUG_MODE=coverage ./vendor/phpunit/phpunit/phpunit --coverage-html tests/report
```

### Run Static Analysis
The library uses PHPStan as a static analysis tool with the default level set to 5.

```shell
./vendor/bin/phpstan analyse
```

### Limitations
Unfortunately, the library does not support a start date of 29, 30, 31 of any month for *`calendar`* financial year type.

We do not expect much use for these dates for a calendar type financial year, as they would cause a considerable problem for accounting.

e.g. if a year starts on 31/1, does the first period end on 28/2? And the following period starts 1/3 effectively skipping a month?

If upon library usage, a user has encountered such a real business issue and can provide a mitigation logic, we can work on implementing it. 

_Important_: This is allowed for a *`business`* type financial year.

### Future Plans
- Update Readme or create Wiki page with more examples of all available methods.

- Introduce new extending library for [CarbonAdapter](https://github.com/roussks/financial-year-carbon) to work directly with [Carbon](https://github.com/briannesbitt/carbon) datetime instances.

- Introduce new extending library for [ChronosAdapter](https://github.com/roussks/financial-year-chronos) to work directly with [Chronos](https://github.com/cakephp/chronos) datetime instances.

- Introduce Laravel Package with different drivers for each of the adapters.

### Versioning
The current library will be using [Semantic Versioning](https://semver.org/)

Non-breaking changes will result in a MINOR or a PATCH version update as classified by SemVer.

Major version releases will not guarantee backwards compatibility.