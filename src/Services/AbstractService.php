<?php
/**
 * Definition of AbstractService
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services;

use FF\Services\Exceptions\ConfigurationException;

/**
 * Class AbstractService
 *
 * @package FF\Services
 */
abstract class AbstractService
{
    use ServiceLocatorTrait;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     * @throws ConfigurationException
     */
    public final function __construct(array $options = [])
    {
        $this->initialize($options);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Retrieves a config option
     *
     * If no option is present indexed with the given $key, the $default value is returned instead.
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Initializes the service
     *
     * Overwrite this method to place custom service initialization logic.
     *
     * @param array $options
     * @throws ConfigurationException
     */
    protected function initialize(array $options)
    {
        $errors = [];
        if (!$this->validateOptions($options, $errors)) {
            throw new ConfigurationException($errors);
        }

        $this->options = $options;
    }

    /**
     * Validates the service's options
     *
     * Fills $errors with messages regarding erroneous service configuration.
     *
     * Overwrite this method to do configuration validation for any concrete service depending on specific options.
     *
     * @param array $options
     * @param string[] $errors
     * @return bool
     */
    protected function validateOptions(array $options, array &$errors): bool
    {
        return true;
    }
}