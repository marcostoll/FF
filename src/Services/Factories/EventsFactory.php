<?php
/**
 * Definition of EventsFactory
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Factories;

use FF\Events\AbstractEvent;
use FF\Factories\AbstractFactory;
use FF\Factories\ClassLocators\BaseNamespaceClassLocator;
use FF\Factories\ClassLocators\ClassLocatorInterface;

/**
 * Class EventsFactory
 *
 * @package FF\Services\Factories
 */
class EventsFactory extends AbstractFactory
{
    /**
     * @var EventsFactory
     */
    protected static $instance;

    /**
     * Declared protected to prevent external usage.
     * Uses a BaseNamespaceClassLocator pre-configured with the 'Events' as common suffix and the FF namespace.
     *
     * @see \FF\Factories\ClassLocators\BaseNamespaceClassLocator
     */
    protected function __construct()
    {
        parent::__construct(new BaseNamespaceClassLocator(AbstractEvent::COMMON_NS_SUFFIX, 'FF'));
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
     * @return EventsFactory
     */
    public static function getInstance(): EventsFactory
    {
        if (is_null(self::$instance)) {
            self::$instance = new EventsFactory();
        }

        return self::$instance;
    }

    /**
     * {@inheritdoc}
     * @return AbstractEvent
     */
    public function create(string $classIdentifier, ...$args)
    {
        /** @var AbstractEvent $event */
        $event = parent::create($classIdentifier, ...$args);
        return $event;
    }
}