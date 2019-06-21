<?php
/**
 * Definition of EventBroker
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services;

use FF\DataStructures\IndexedCollection;
use FF\DataStructures\OrderedCollection;
use FF\Events\AbstractEvent;
use FF\Factories\Exceptions\ClassNotFoundException;
use FF\Services\Factories\EventsFactory;

/**
 * Class EventBroker
 *
 * @package FF\Services
 */
class EventBroker extends AbstractService
{
    /**
     * @var IndexedCollection
     */
    protected $subscriptions;

    /**
     * @var EventsFactory
     */
    protected $eventsFactory;

    /**
     * {@inheritDoc}
     */
    protected function initialize(array $options)
    {
        parent::initialize($options);

        $this->subscriptions = new IndexedCollection();
        $this->eventsFactory = EventsFactory::getInstance();
    }

    /**
     * @return EventsFactory
     */
    public function getEventsFactory(): EventsFactory
    {
        return $this->eventsFactory;
    }

    /**
     * @return IndexedCollection
     */
    public function getSubscriptions(): IndexedCollection
    {
        return $this->subscriptions;
    }

    /**
     * @param string $classIdentifier
     * @return OrderedCollection
     */
    public function getSubscribers(string $classIdentifier): OrderedCollection
    {
        $this->initializeSubscribersCollection($classIdentifier);
        return $this->subscriptions->get($classIdentifier);
    }

    /**
     * Appends a listener to the subscribers list of an event
     *
     * Removes any previous subscriptions of the listener first to the named event.
     *
     * @param callable $listener
     * @param string $classIdentifier
     * @return $this
     */
    public function subscribe(callable $listener, string $classIdentifier)
    {
        $this->unsubscribe($listener, $classIdentifier);
        $this->initializeSubscribersCollection($classIdentifier);

        $this->subscriptions->get($classIdentifier)->push($listener);
        return $this;
    }

    /**
     * Prepends a listener to the subscriber list of an event
     *
     * Removes any previous subscriptions of the listener first to the named event.
     *
     * @param callable $listener
     * @param string $classIdentifier
     * @return $this
     */
    public function subscribeFirst(callable $listener, string $classIdentifier)
    {
        $this->unsubscribe($listener, $classIdentifier);
        $this->initializeSubscribersCollection($classIdentifier);

        $this->subscriptions->get($classIdentifier)->unshift($listener);
        return $this;
    }

    /**
     * Unsubscribes a listener
     *
     * If $name is omitted, the listener will be unsubscribed from each event it was subscribed to.
     *
     * @param callable $listener
     * @param string $classIdentifier
     * @return $this
     */
    public function unsubscribe(callable $listener, string $classIdentifier = null)
    {
        /** @var OrderedCollection $listenerCollection */
        foreach ($this->subscriptions as $name => $listenerCollection) {
            if (!is_null($classIdentifier) && $classIdentifier != $name) continue;

            $index = $listenerCollection->search($listener, true);
            if (is_null($index)) continue;

            // remove listener from event
            unset($listenerCollection[$index]);
        }

        return $this;
    }

    /**
     * Removes all subscriptions for this event
     *
     * @param string $classIdentifier
     * @return $this
     */
    public function unsubscribeAll(string $classIdentifier)
    {
        unset($this->subscriptions[$classIdentifier]);
        return $this;
    }

    /**
     * Checks whether any listeners where subscribed to the named event
     *
     * @param string $classIdentifier
     * @return bool
     */
    public function hasSubscribers(string $classIdentifier): bool
    {
        return $this->subscriptions->has($classIdentifier) && !$this->subscriptions->get($classIdentifier)->isEmpty();
    }

    /**
     * Checks of the listener has been subscribed to the given event
     *
     * @param callable $listener
     * @param string $classIdentifier
     * @return bool
     */
    public function isSubscribed(callable $listener, string $classIdentifier): bool
    {
        if (!$this->hasSubscribers($classIdentifier)) return false;

        return !is_null($this->subscriptions->get($classIdentifier)->search($listener));
    }

    /**Notifies all listeners of events of the given type
     *
     * Listeners will be notified in the order of their subscriptions.
     * Does nothing if no listeners subscribed to the type of the event.
     *
     * Creates an event instance and fires it.
     * Does nothing if no suitable event model could be created.
     *
     * Any given $args will be passed to the constructor of the suitable event
     * model class in the given order.
     *
     * @param string $classIdentifier
     * @param mixed ...$args
     * @return $this
     */
    public function fire(string $classIdentifier, ...$args)
    {
        $event = $this->createEvent($classIdentifier, ...$args);
        if (is_null($event)) return $this;

        foreach ($this->getSubscribers($classIdentifier) as $listener) {
            $this->notify($listener, $event);

            if ($event->isCanceled()) {
                // stop notifying further listeners if event has been canceled
                break;
            }
        }

        return $this;
    }

    /**
     * Initialize listener collection if necessary
     *
     * @param string $classIdentifier
     */
    protected function initializeSubscribersCollection(string $classIdentifier)
    {
        if ($this->subscriptions->has($classIdentifier)) return;

        $this->subscriptions->set($classIdentifier, new OrderedCollection());
    }

    /**
     * Create a fresh event instance
     *
     * @param string $classIdentifier
     * @param mixed ...$args
     * @return AbstractEvent|null
     */
    protected function createEvent(string $classIdentifier, ...$args): ?AbstractEvent
    {
        try {
            return $this->eventsFactory->create($classIdentifier, ...$args);
        } catch (ClassNotFoundException $e) {
            return null;
        }
    }

    /**
     * Passes the event to the listener
     *
     * The listener will be invoked with the event as the first and only argument.
     * Any return values of the listener will be discarded.
     *
     * @param callable $listener
     * @param AbstractEvent $event
     */
    protected function notify(callable $listener, AbstractEvent $event)
    {
        call_user_func($listener, $event);
    }
}