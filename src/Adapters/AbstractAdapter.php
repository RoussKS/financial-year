<?php

namespace RoussKS\FinancialYear\Adapters;

use RoussKS\Enums\TypeEnum;
use RoussKS\FinancialYear\Exceptions\ConfigException;

/**
 * Class AbstractAdapter
 *
 * @package RoussKS\FinancialYear\Adapters
 */
abstract class AbstractAdapter
{
    /**
     * @var mixed
     */
    protected $fy;

    /**
     * @var TypeEnum
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $fyStartDate;

    /***
     * @var mixed
     */
    protected $fyEndDate;

    /**
     * Applicable to Business TypeEnum only.
     *
     * @var int
     */
    protected $fyWeeks;

    /**
     * AbstractAdapter constructor.
     *
     * @param  string $type
     *
     * @return void
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function __construct(string $type)
    {
        if ($type === null) {
            $this->throwConfigurationException('Invalid financial year type, only calendar & business are allowed');
        }

        $this->type = TypeEnum::get($type);
    }

    /**
     * @return TypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getFyWeeks()
    {
        return $this->fyWeeks;
    }

    /**
     * Set the number of weeks for the Financial Year.
     *
     * Only applies to business TypeEnum
     *
     * @param int $fyWeeks
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setFyWeeks(int $fyWeeks)
    {
        if ($this->type->isNot(TypeEnum::BUSINESS())) {
            $this->throwConfigurationException('Can not set the financial year weeks property for non business year type');
        }

        $this->fyWeeks = $fyWeeks;
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
            $this->fy === null ||
            $this->type === null ||
            $this->fyStartDate === null
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