<?php
/**
 * Definition of Bootstrap
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Runtime;

use Composer\Autoload\ClassLoader;
use FF\Factories\ServicesFactory;
use FF\Factories\SF;
use FF\Runtime\Exceptions\BootstrapException;
use FF\Services\Exceptions\ResourceInvalidException;
use FF\Services\Runtime\ConfigParser;

/**
 * Class Bootstrap
 *
 * @package FF\Runtime
 */
class Bootstrap
{
    /**
     * Environment labels
     */
    const ENV_PROD = 'production';
    const ENV_DEV = 'development';
    const ENV_TEST = 'testing';

    /**
     * Adds a namespace location to Composer's class loader
     *
     * @param ClassLoader $loader
     * @param string $namespace
     * @param string $src
     * @param bool $prepend
     * @return $this
     */
    public function addNamespace(ClassLoader $loader, string $namespace, string $src, bool $prepend = false)
    {
        $loader->addPsr4(rtrim($namespace, '\\') . '\\', rtrim($src, '/') . '/', $prepend);
        return $this;
    }

    /**
     * Stores initial data in the system-wide registry
     *
     * Sets keys 'basePath' and 'environment' using the given argument values.
     *
     * @param string $basePath
     * @param string $environment
     * @param array $additionalData
     * @return $this
     */
    public function initRegistry(string $basePath, string $environment = self::ENV_DEV, array $additionalData = [])
    {
        $data = array_merge([
            'basePath' => $basePath,
            'environment' => $environment
        ], $additionalData);
        Registry::getInstance()->setData($data);
        return $this;
    }

    /**
     * Initializes the services factory
     *
     * If $environment is omitted, tries to retrieve it from the Registry.
     * If then an non-empty $environment is present, looks for an environment-specific services configuration
     * and merges its values into the given $servicesYml.
     *
     * @param string $servicesYml
     * @param array $replacements
     * @param string|null $environment
     * @return $this
     * @throws BootstrapException
     */
    public function initServiceFactory(string $servicesYml, array $replacements = [], string $environment = null)
    {
        if (!is_file($servicesYml) || !is_readable($servicesYml)) {
            throw new BootstrapException('services yml [' . $servicesYml . '] not found or not readable');
        }
        if (is_null($environment)) {
            $environment = Registry::getInstance()->getField('environment');
        }

        try {
            $configParser = new ConfigParser();

            $contents = $configParser->load($servicesYml, $replacements);
            $servicesConfig = $configParser->parse($contents);

            do {
                if (empty($environment)) {
                    break;
                }

                $envYml = $this->buildEnvironmentServicesYmlFileName($servicesYml, $environment);
                $envContents = $configParser->load($envYml, $replacements);
                if (empty($envContents)) {
                    // do nothing if no env-specific config file is present
                    break;
                }
                $envConfig = $configParser->parse($envContents);

                $servicesConfig = $configParser->merge($servicesConfig, $envConfig);
            } while (false);
        } catch (ResourceInvalidException $exception) {
            throw new BootstrapException('error while parsing services configuration', 0, $exception);
        }

        SF::setInstance(new ServicesFactory($servicesConfig));

        return $this;
    }

    /**
     * Invoke an arbitrary callable
     *
     * Any argument beyond the first will be passed to the callable in the given order.
     *
     * @param callable $callable
     * @return $this
     */
    public function invoke(callable $callable)
    {
        $args = array_slice(func_get_args(), 1);
        if (empty($args)) {
            $callable();
        } else {
            call_user_func_array($callable, $args);
        }

        return $this;
    }

    /**
     * Retrieves the file name with the suffix injected
     *
     * @param string $servicesYml
     * @param string $environment
     * @param string $delimiter
     * @return string
     */
    protected function buildEnvironmentServicesYmlFileName(
        string $servicesYml,
        string $environment,
        string $delimiter = '-'
    ) {
        $pathInfo = pathinfo($servicesYml);

        if (!isset($pathInfo['extension'])) {
            return $servicesYml . $delimiter . $environment;
        }

        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR
            . $pathInfo['filename']
            . $delimiter . $environment
            . '.' . $pathInfo['extension'];
    }
}
