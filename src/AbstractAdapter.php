<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;

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

    protected ?string $type = null;
    protected ?DateTimeInterface $fyStartDate = null;
    protected ?DateTimeInterface $fyEndDate = null;

    /**
     * Applicable to Business financial year type only.
     */
    protected ?int $fyWeeks = null;

    /**
     * The number of fyPeriods for the selected financial year type.
     */
    protected ?int $fyPeriods = null;

    /**
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
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the number of weeks for business type financial year or null for calendar type.
     */
    public function getFyWeeks(): ?int
    {
        return $this->fyWeeks;
    }

    /**
     * Get the number of periods of the financial year.
     */
    public function getFyPeriods(): int
    {
        return $this->fyPeriods;
    }

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business financial year type and will be set either 52 or 53.
     * Throw ConfigException otherwise.
     *
     * @throws ConfigException
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void
    {
        if (!$this->isBusinessType($this->getType())) {
            $this->throwConfigurationException(
                'Can not set the financial year weeks property for non business year type.'
            );
        }

        $this->fyWeeks = $fiftyThreeWeeks ? 53 : 52;
    }

    /**
     * Validate configuration.
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
     * @throws Exception
     */
    protected function validatePeriodId(int $id): void
    {
        if ($id < 1 || $id > $this->getFyPeriods()) {
            throw new Exception('There is no period with id: ' . $id . '.');
        }
    }

    /**
     * Validate fyType is business and week $id is between 1 and fyWeeks property (52 or 53).
     *
     * @throws Exception
     * @throws ConfigException
     */
    protected function validateBusinessWeekId(int $id): void
    {
        if (!$this->isBusinessType($this->getType())) {
            $this->throwConfigurationException('Week id is not applicable for non business type financial year.');
        }

        if ($id < 1 || $id > $this->getFyWeeks()) {
            throw new Exception('There is no week with id: ' . $id . '.');
        }
    }

    /**
     * Check if calendar type financial year.
     */
    protected function isCalendarType(string $value): bool
    {
        return $value === self::TYPE_CALENDAR;
    }

    /**
     * Check if business type financial year.
     */
    protected function isBusinessType(string $value): bool
    {
        return $value === self::TYPE_BUSINESS;
    }

    /**
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
