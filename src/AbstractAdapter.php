<?php

declare(strict_types=1);

namespace RoussKS\FinancialYear;

use DateTimeInterface;
use RoussKS\FinancialYear\Exceptions\ConfigException;
use RoussKS\FinancialYear\Exceptions\Exception;
use ValueError;

abstract class AbstractAdapter
{
    protected Type $type;
    protected DateTimeInterface $fyStartDate;
    protected DateTimeInterface $fyEndDate;

    /**
     * Applicable to Business financial year type only.
     */
    protected int|null $fyWeeks = null;

    /**
     * The number of fyPeriods for the selected financial year type.
     */
    protected int $fyPeriods;

    /**
     * @return void
     *
     * @throws ConfigException
     */
    public function __construct(Type|string $type, bool $fiftyThreeWeeks = false)
    {
        if (is_string($type)) {
            try {
                $type = Type::from($type);
            } catch (ValueError) {
                $this->throwConfigurationException(message: 'Invalid Financial Year Type.');
            }
        }

        $this->type = $type;

        // We only support 2 types of financial years: Calendar and Business.
        // First check for calendar, otherwise it's business.

        // Calendar Type has 12 periods.
        if ($this->type->isCalendar()) {
            $this->fyPeriods = 12;

            return;
        }

        // Business Type has 13 periods.
        $this->fyPeriods = 13;
        $this->setFyWeeks(fiftyThreeWeeks: $fiftyThreeWeeks);
    }

    /**
     * Get the financial year type.
     */
    public function getType(): Type
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
     * Throw ConfigException for calendar type.
     *
     * @throws ConfigException
     */
    public function setFyWeeks(bool $fiftyThreeWeeks = false): void
    {
        if ($this->getType()->isNotBusiness()) {
            $this->throwConfigurationException(
                message: 'Can not set the financial year weeks property for non business year type.'
            );
        }

        $this->fyWeeks = $fiftyThreeWeeks ? 53 : 52;
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
            throw new Exception(message: 'There is no period with id: ' . $id . '.');
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
        if ($this->getType()->isNotBusiness()) {
            $this->throwConfigurationException(
                message: 'Week id is not applicable for non business type financial year.'
            );
        }

        if ($id < 1 || $id > $this->getFyWeeks()) {
            throw new Exception(message: 'There is no week with id: ' . $id . '.');
        }
    }

    /**
     * @throws ConfigException
     */
    protected function throwConfigurationException(?string $message = null): void
    {
        if ($message === null) {
            $message = 'Invalid configuration of financial year adapter.';
        }

        throw new ConfigException(message: $message);
    }
}
