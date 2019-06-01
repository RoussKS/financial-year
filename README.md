# \RoussKS\FinancialYear - Financial Year PHP Library

[![Build Status](https://travis-ci.com/RoussKS/financial-year.svg?branch=master)](https://travis-ci.com/RoussKS/financial-year)

## v0.9.1
v1.0.0 will be released when all testing is completed and a test suite is added.

### Purpose

This library aims to solve a business problem in a consistent manner.

The calculation of week, period, year and end dates of those for a business.

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
     
### Basic Use
```php
$startDate = new \DateTime('2019-01-01');

$config = [
    'fyType' => 'calendar',
    'fyStartDate' => $startDate,
];

/** \RoussKS\FinancialYear\Interfaces\AdapterInterface $fy */
$fy = (new \RoussKS\FinancialYear\FinancialYear($startDate, $config))->getAdapter();

echo $fy->getFyEndDate()->format('Y-m-d'); // 2019-12-31 
```

### Notes
Even though an adapter can be instantiated directly, we advise to instantiate the library from the main class.

### Limitations
Unfortunately, the library does not support a start date of February 29th (29/02/YYYY) for *`calendar`* financial year type.

This was a conscious decision as we do not expect anyone to select that date to open a business and use a year/calendar financial year.

This is allowed for a *`business`* type financial year. 

### Roadmap
1.1 Introduce [Carbon Adapter](https://github.com/briannesbitt/carbon) to work directly with Carbon datetime instances.
1.2 Introduce [Chronos Adapter](https://github.com/cakephp/chronos) to work directly with Chronos datetime instances

### Versioning
The current library will be using [Semantic Versioning](https://semver.org/)

Non-breaking changes will result in a MINOR or a PATCH version update as classified by SemVer.

Major version releases will not guarantee backwards compatibility.