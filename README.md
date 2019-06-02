# \RoussKS\FinancialYear - Financial Year PHP Library

[![Latest Version](https://img.shields.io/github/release/RoussKS/financial-year.svg?style=flat-square)](https://github.com/RoussKS/financial-year/releases)
[![Build Status](https://travis-ci.com/RoussKS/financial-year.svg?branch=master)](https://travis-ci.com/RoussKS/financial-year)
[![codecov](https://codecov.io/gh/RoussKS/financial-year/branch/master/graph/badge.svg)](https://codecov.io/gh/RoussKS/financial-year)

### Introduction / Background / Purpose
In my first working environment as a software/web developer in UK, I stumbled upon the need to do different calculations for financial year needs.
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
     >The "fiscal year end" (FYE) is the date that marks the end of the fiscal year. Some companies—such as Cisco Systems[1]—end their fiscal year on the same day of the week each year, e.g. the day that is closest to a particular date (for example, the Friday closest to 31 December). Under such a system, some fiscal years will have 52 weeks and others 53 weeks.
   - A financial year of this type, always has 364 days a year and is divided in 13 periods (each period has 4 weeks, 28 days).
   - The current library will accommodate the 52-53 weeks methodology of financial year, 
     where a 53rd week is added at the end of a financial year in order to cover for missing days from the previous 364 day years.
     This is a business domain decision, hence the library does not calculate when a year should be 52 or 53 years.
     It needs to be set by the user.

## Version
v1.0.0 is now released.

### Requirements
- PHP Version > 7.1

### Installation
```console
composer require roussks/financial-year
```

### Basic Use
```php
// DateTimeAdapter
$startDate = new \DateTime('2019-01-01');

$fy = new \RoussKS\FinancialYear\DateTimeAdapter('calendar', $startDate);

echo $fy->getFyEndDate()->format('Y-m-d'); // 2019-12-31 
```

### Limitations
Unfortunately, the library does not support a start date of February 29th (29/02/YYYY) for *`calendar`* financial year type.

This was a conscious decision as we do not expect anyone to select that date to open a business and use a year/calendar financial year.

This is allowed for a *`business`* type financial year. 

### Contribute
Do you want to make a change? Pull requests are welcome with fully tested code.

### Roadmap
Introduce new extending library for [CarbonAdapter](https://github.com/briannesbitt/carbon) to work directly with Carbon datetime instances.

Introduce new extending library for [ChronosAdapter](https://github.com/cakephp/chronos) to work directly with Chronos datetime instances.

### Versioning
The current library will be using [Semantic Versioning](https://semver.org/)

Non-breaking changes will result in a MINOR or a PATCH version update as classified by SemVer.

Major version releases will not guarantee backwards compatibility.