# Financial Year PHP Library

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
     

### Basic Use
```php
$startDate = new \DateTime('2019-01-01');

$config = [
    'fyType' => 'calendar'
    'fyStartDate' => $startDate,
]

/** \RoussKS\FinancialYear\Interfaces\AdapterInterface $fy */
$fy = (new \RoussKS\FinancialYear\FinancialYear($startDate, $config))->getAdapter();

echo $fy->getFyEndDate()->format('Y-m-d'); // 2019-12-31 
```

### Limitations
Unfortunately, the library does not support a start date of February 29th (29/02/YYYY) for *`calendar`* financial year type.

This was a conscious decision as we do not expect anyone to select that date to open a business and use a year/calendar financial year.

This is allowed for a *`business`* type financial year. 

### Versioning
The current library will be using [Semantic Versioning](https://semver.org/)

Non-breaking changes will result in a MINOR or a PATCH version update as classified by SemVer.

Major version releases will not guarantee backwards compatibility.