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

use FF\Factories\ClassLocators\ClassIdentifierAwareInterface;
use FF\Services\Exceptions\ConfigurationException;
use FF\Services\Traits\EventEmitterTrait;
use FF\Services\Traits\ServiceLocatorTrait;

/**
 * Class AbstractService
 *
 * @package FF\Services
 */
abstract class AbstractService implements ClassIdentifierAwareInterface
{
    use EventEmitterTrait, ServiceLocatorTrait;

    /**
     * For use with the BaseNamespaceClassLocator of the ServicesFactory
     */
    const COMMON_NS_SUFFIX = 'Services';

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     * @throws ConfigurationException
     */
    final public function __construct(array $options = [])
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
     * {@inheritDoc}
     */
    public static function getClassIdentifier(): string
    {
        $className = get_called_class();
        $needle = '\\' . self::COMMON_NS_SUFFIX . '\\';
        $pos = strpos($className, $needle);
        if ($pos === false) {
            return $className;
        }

        return substr($className, $pos + strlen($needle));
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
