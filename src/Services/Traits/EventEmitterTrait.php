<?php
/**
 * Definition of EventEmitterTrait
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Traits;

use FF\Services\EventBroker;
use FF\Services\Factories\SF;

/**
 * Trait EventEmitterTrait
 *
 * @package FF\Services\Traits
 */
trait EventEmitterTrait
{
    /**
     * Creates an event instance and fires it
     *
     * Delegates the execution to the EventBroker provided by the ServiceFactory.
     *
     * @param string $classIdentifier
     * @param mixed ...$args
     * @return $this
     */
    protected function fire(string $classIdentifier, ...$args)
    {
        /** @var EventBroker $eventBroker */
        static $eventBroker = null;

        if (is_null($eventBroker)) {
            $eventBroker = SF::i()->get('EventBroker');
        }

        $eventBroker->fire($classIdentifier, ...$args);

        return $this;
    }
}