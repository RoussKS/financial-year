<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\FinancialYear\Enums\TypeEnum;
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
     * @var \DateTimeInterface
     */
    protected $fyStartDate;

    /***
     * @var \DateTimeInterface
     */
    protected $fyEndDate;

    /**
     * Applicable to Business TypeEnum only.
     *
     * @var int|null
     */
    protected $fyWeeks = null;

    /**
     * AbstractAdapter constructor.
     *
     * @param  string $type
     * @param  bool $fiftyThreeWeeks
     *
     * @return void
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function __construct(string $type, bool $fiftyThreeWeeks)
    {
        if ($type === null) {
            $this->throwConfigurationException('Financial year type cannot be null');
        }

        $this->type = TypeEnum::get($type);

        if ($this->type->is(TypeEnum::BUSINESS())) {
            $this->setFyWeeks($fiftyThreeWeeks);
        }
    }

    /**
     * Get the financial year's type.
     *
     * @return TypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * This is always set on construct, so it would never return null (as set in the property).
     *
     * @return int
     */
    public function getFyWeeks()
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
    public function setFyWeeks($fiftyThreeWeeks = false)
    {
        if ($this->type->isNot(TypeEnum::BUSINESS())) {
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
    public function validate()
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
     * @param  null $message
     *
     * @throws ConfigException
     */
    protected function throwConfigurationException($message = null)
    {
        if ($message === null) {
            $message = 'Invalid configuration of financial year adapter';
        }

        throw new ConfigException($message);
    }
}