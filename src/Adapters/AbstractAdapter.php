<?php

namespace RoussKS\FinancialYear\Adapters;

use DateTimeInterface;
use RoussKS\FinancialYear\Enums\TypeEnum;
use RoussKS\FinancialYear\Exceptions\Exception;
use RoussKS\FinancialYear\Exceptions\ConfigException;

/**
 * Class AbstractAdapter
 *
 * @package RoussKS\FinancialYear\Adapters
 */
abstract class AbstractAdapter
{
    /**
     * @var TypeEnum
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
     * Applicable to Business TypeEnum only.
     *
     * @var int|null
     */
    protected $fyWeeks;

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
    public function __construct(string $type, bool $fiftyThreeWeeks)
    {
        if (TypeEnum::isCalendar($type)) {
            $this->type = $type;

            return;
        }

        if (TypeEnum::isBusiness($type)) {
            $this->type = $type;
            $this->setFyWeeks($fiftyThreeWeeks);

            return;
        }

        $this->throwConfigurationException('Invalid Financial Year Type');
    }

    /**
     * Get the financial year's type.
     *
     * @return TypeEnum
     */
    public function getType(): TypeEnum
    {
        return $this->type;
    }

    /**
     * This is always set on construct, so it would never return null (as set in the property).
     *
     * @return int
     */
    public function getFyWeeks(): int
    {
        return $this->fyWeeks;
    }

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business TypeEnum and will be set either 52 or 53.
     *
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyWeeks($fiftyThreeWeeks = false): void
    {
        if (!TypeEnum::isBusiness($this->type)) {
            $this->throwConfigurationException('Can not set the financial year weeks property for non business year type');
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
    public function validate(): void
    {
        if (
            $this->type === null ||
            $this->fyStartDate === null ||
            $this->fyEndDate === null
        ) {
            $this->throwConfigurationException();
        }
    }

    /**
     * Validate period $id is between 1 and 12.
     *
     * @param  int $id
     *
     * @return void
     *
     * @throws Exception
     */
    protected function validatePeriodId(int $id): void
    {
        if ($id < 1 || $id > 12) {
            throw new Exception('There is no period with id: ' . $id);
        }
    }

    /**
     * Validate fyType is business and week $id is between 1 and the set fyWeeks (52 or 53).
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
        if (!TypeEnum::isBusiness($this->type)) {
            $this->throwConfigurationException('Week id is not applicable for non business type financial year');
        }

        if ($id < 1 || $id > $this->fyWeeks) {
            throw new Exception('There is no week with id: ' . $id);
        }
    }

    /**
     * @param  null $message
     *
     * @throws ConfigException
     */
    protected function throwConfigurationException($message = null): void
    {
        if ($message === null) {
            $message = 'Invalid configuration of financial year adapter';
        }

        throw new ConfigException($message);
    }
}