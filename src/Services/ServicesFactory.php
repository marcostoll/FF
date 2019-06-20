<?php
/**
 * Definition of ServicesFactory
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services;

use FF\Factories\AbstractSingletonFactory;
use FF\Factories\ClassLocators\ClassLocatorInterface;
use FF\Factories\ClassLocators\NamespaceClassLocator;
use FF\Factories\Exceptions\ClassNotFoundException;
use FF\Services\Exceptions\ConfigurationException;

/**
 * Class ServicesFactory
 *
 * @package FF\Services
 */
class ServicesFactory extends AbstractSingletonFactory
{
    /**
     * @var ServicesFactory
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $servicesOptions;

    /**
     * Uses a NamespaceClassLocator pre-configured with the FF\Services namespace.
     * @param array $servicesOptions
     * @see \FF\Factories\ClassLocators\NamespaceClassLocator
     */
    public function __construct(array $servicesOptions = [])
    {
        parent::__construct(new NamespaceClassLocator(__NAMESPACE__));

        $this->servicesOptions = $servicesOptions;
    }

    /**
     * Sets the singleton instance of this class
     *
     * @param ServicesFactory $instance
     */
    public static function setInstance(ServicesFactory $instance)
    {
        self::$instance = $instance;
    }

    /**
     * Retrieves the singleton instance of this class
     *
     * @return ServicesFactory
     * @throws ConfigurationException
     */
    public static function getInstance(): ServicesFactory
    {
        if (is_null(self::$instance)) {
            throw new ConfigurationException(['singleton instance of the service factory has not been initialized']);
        }

        return self::$instance;
    }


    /**
     * Removes the singleton instance of this class
     */
    public static function clearInstance()
    {
        self::$instance = null;
    }


    /**
     * Retrieves one ro more fully initialized services
     *
     * Returns a single service instance if only one class identifier was given as argument.
     * Returns an array of service instances instead if two or more class identifiers were passed. The returned list
     * will ordered in the same way as the class identifier arguments have been passed.
     *
     * @param string ...$classIdentifiers
     * @return AbstractService|AbstractService[]
     * @throws ClassNotFoundException
     * @throws ConfigurationException
     */
    public function get(string ...$classIdentifiers)
    {
        $services = [];
        foreach ($classIdentifiers as $classIdentifier)
        {
            $services[] = parent::create($classIdentifier, $this->getServiceOptions($classIdentifier));
        }

        return count($services) == 1 ? $services[0] : $services;
    }

    /**
     * {@inheritdoc}
     * @return AbstractService
     */
    public function create(string $classIdentifier, ...$args)
    {
        /** @var AbstractService $service */
        $service = parent::create($classIdentifier, ...$args);
        return $service;
    }

    /**
     * {@inheritDoc}
     * @return NamespaceClassLocator
     */
    public function getClassLocator(): ClassLocatorInterface
    {
        return parent::getClassLocator();
    }

    /**
     * Retrieves the options for a specific service
     *
     * @param string $classIdentifier
     * @return array
     */
    public function getServiceOptions(string $classIdentifier)
    {
        return $this->servicesOptions[$classIdentifier] ?? [];
    }
}