<?php

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;

/**
 * Class AbstractAdapter
 *
 * @package RoussKS\FinancialYear
 */
abstract class AbstractAdapter
{
    /**
     * The financial year calendar type constant.
     */
    public const TYPE_CALENDAR = 'calendar';

    /**
     * The financial year business type constant.
     */
    public const TYPE_BUSINESS = 'business';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var DateTimeInterface
     */
    protected $fyStartDate;

    /***
     * @var DateTimeInterface
     */
    protected $fyEndDate;

    /**
     * Applicable to Business financial year type only.
     *
     * @var int|null
     */
    protected $fyWeeks;

    /**
     * The number of fyPeriods for the selected financial year type.
     *
     * @var int
     */
    protected $fyPeriods;

    /**
     * AbstractAdapter constructor.
     *
     * @param  string $type
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function __construct(string $type, bool $fiftyThreeWeeks = false)
    {
        // Calendar Type has 12 periods.
        if ($this->isCalendarType($type)) {
            $this->type = $type;
            $this->fyPeriods = 12;

            return;
        }

        // Business Type has 13 periods.
        if ($this->isBusinessType($type)) {
            $this->type = $type;
            $this->fyPeriods = 13;
            $this->setFyWeeks($fiftyThreeWeeks);

            return;
        }

        $this->throwConfigurationException('Invalid Financial Year Type.');
    }

    /**
     * Get the financial year type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the number of weeks for business type financial year or null for calendar type.
     *
     * @return int|null
     */
    public function getFyWeeks(): ?int
    {
        return $this->fyWeeks;
    }

    /**
     * Get the number of periods of the financial year.
     *
     * @return int
     */
    public function getFyPeriods(): int
    {
        return $this->fyPeriods;
    }

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business financial year type and will be set either 52 or 53.
     * Throw ConfigException for calendar type.
     *
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyWeeks($fiftyThreeWeeks = false): void
    {
        if (!$this->isBusinessType($this->type)) {
            $this->throwConfigurationException(
                'Can not set the financial year weeks property for non business year type.'
            );
        }

        $this->fyWeeks = 53;

        if (!$fiftyThreeWeeks) {
            $this->fyWeeks = 52;
        }
    }

    /**
     * Validate configuration.
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function validateConfiguration(): void
    {
        if ($this->type === null || $this->fyStartDate === null || $this->fyEndDate === null) {
            $this->throwConfigurationException();
        }
    }

    /**
     * Validate period $id is between 1 and 12 for calendar type financial year.
     * Or between 1 and 13 for business type financial year.
     *
     * @param  int $id
     *
     * @return void
     *
     * @throws Exception
     */
    protected function validatePeriodId(int $id): void
    {
        if ($id < 1 || $id > $this->fyPeriods) {
            throw new Exception('There is no period with id: ' . $id . '.');
        }
    }

    /**
     * Validate fyType is business and week $id is between 1 and fyWeeks property (52 or 53).
     *
     * @param  int $id
     *
     * @return void
     *
     * @throws Exception
     * @throws ConfigException
     */
    protected function validateBusinessWeekId(int $id): void
    {
        if (!$this->isBusinessType($this->type)) {
            $this->throwConfigurationException('Week id is not applicable for non business type financial year.');
        }

        if ($id < 1 || $id > $this->fyWeeks) {
            throw new Exception('There is no week with id: ' . $id . '.');
        }
    }

    /**
     * Check if calendar type financial year.
     *
     * @param  string $value
     *
     * @return bool
     */
    protected function isCalendarType(string $value): bool
    {
        return $value === self::TYPE_CALENDAR;
    }

    /**
     * Check if business type financial year.
     *
     * @param  string $value
     *
     * @return bool
     */
    protected function isBusinessType(string $value): bool
    {
        return $value === self::TYPE_BUSINESS;
    }

    /**
     * @param  string|null $message
     *
     * @return void
     *
     * @throws ConfigException
     */
    protected function throwConfigurationException(string $message = null): void
    {
        if ($message === null) {
            $message = 'Invalid configuration of financial year adapter.';
        }

        throw new ConfigException($message);
    }
}
