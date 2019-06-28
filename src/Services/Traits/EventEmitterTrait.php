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

use FF\Factories\SF;
use FF\Services\Events\EventBroker;

/**
 * Trait EventEmitterTrait
 *
 * @package FF\Services\Traits
 */
trait EventEmitterTrait
{
    /**
     * @var bool
     */
    protected $fireEvents = true;

    /**
     * @return bool
     */
    public function hasFireEvents(): bool
    {
        return $this->fireEvents;
    }

    /**
     * @param bool $fireEvents
     * @return $this
     */
    public function setFireEvents(bool $fireEvents)
    {
        $this->fireEvents = $fireEvents;
        return $this;
    }

    /**
     * Creates an event instance and fires it
     *
     * Does nothing if $fireEvents is turned of.
     *
     * Delegates the execution to the EventBroker provided by the ServiceFactory.
     *
     * @param string $classIdentifier
     * @param mixed ...$args
     * @return $this
     */
    protected function fire(string $classIdentifier, ...$args)
    {
        if (!$this->hasFireEvents()) {
            return $this;
        }

        /** @var EventBroker $eventBroker */
        static $eventBroker = null;

        if (is_null($eventBroker)) {
            $eventBroker = SF::i()->get('Events\EventBroker');
        }

        $eventBroker->fire($classIdentifier, ...$args);

        return $this;
    }
}
