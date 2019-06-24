<?php
/**
 * Definition of ControllersFactory
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Factories;


use FF\Controllers\AbstractController;
use FF\Factories\ClassLocators\BaseNamespaceClassLocator;
use FF\Factories\ClassLocators\ClassLocatorInterface;

/**
 * Class ControllersFactory
 *
 * @package FF\Factories
 */
class ControllersFactory extends AbstractSingletonFactory
{
    /**
     * @var ControllersFactory
     */
    protected static $instance;

    /**
     * Declared protected to prevent external usage.
     * Uses a BaseNamespaceClassLocator pre-configured with the 'Events' as common suffix and the FF namespace.
     * @see \FF\Factories\ClassLocators\BaseNamespaceClassLocator
     */
    protected function __construct()
    {
        parent::__construct(new BaseNamespaceClassLocator(AbstractController::COMMON_NS_SUFFIX, 'FF'));
    }

    /**
     * Declared protected to prevent external usage
     */
    protected function __clone()
    {

    }

    /**
     * {@inheritDoc}
     * @return BaseNamespaceClassLocator
     */
    public function getClassLocator(): ClassLocatorInterface
    {
        return parent::getClassLocator();
    }

    /**
     * Retrieves the singleton instance of this class
     *
     * @return ControllersFactory
     */
    public static function getInstance(): ControllersFactory
    {
        if (is_null(self::$instance)) {
            self::$instance = new ControllersFactory();
        }

        return self::$instance;
    }

    /**
     * {@inheritdoc}
     * @return AbstractController
     */
    public function create(string $classIdentifier, ...$args)
    {
        /** @var AbstractController $controller */
        $controller = parent::create($classIdentifier, ...$args);
        return $controller;
    }
}