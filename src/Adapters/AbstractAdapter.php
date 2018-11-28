<?php

namespace RoussKS\FinancialYear\Adapters;

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
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $startDate;

    /**
     * @var string
     */
    protected $endDate;

    /**
     * @var array
     */
    protected $types = ['calendar', 'business'];

    /**
     * AbstractAdapter constructor.
     *
     * @param  null|string $type
     * @param  string|null $startDate
     * @param  string|null $endDate
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function __construct(string $type = null, string $startDate = null, string $endDate = null)
    {
        if ($type !== null) {
            if (!\in_array($type, $this->types, true)) {
                $this->throwConfigurationException('Invalid financial year type, only calendar & business are allowed');
            }

            $this->type = $type;
        }

        if ($startDate !== null) {
            $this->startDate = $startDate;
        }

        if ($endDate !== null) {
            $this->startDate = $endDate;
        }
    }

    /**
     * @param  string $type
     *
     * @return void
     *
     * @throws ConfigException
     */
    public function setType(string $type)
    {
        if ($type === null || !\in_array($type, $this->types, true)) {
            $this->throwConfigurationException('Invalid financial year type, only calendar & business are allowed');
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
            $this->fy === null ||
            $this->type === null ||
            $this->startDate === null
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