Fast Forward
========================================================================================================================

by Marco Stoll

- <marco.stoll@rocketmail.com>
- <http://marcostoll.de>
- <https://github.com/marcostoll>
- <https://github.com/marcostoll/FF>
------------------------------------------------------------------------------------------------------------------------

# Introduction - What is Fast Forward?

**Fast Forward** (in short **FF**) is a generic application template for building web and/or command line applications 
fast and easy. It addresses the common tasks and provides configurable and/or extendable default implementations for you
to use.

Currently **FF** is composed of the following features:
1. Services and the Service Factory
2. Events and the Event Broker

More features will follow (see the Road Map section below).

# A Warning before you start

**FF** is highly opinionated and depends on a bunch of conventions! So be sure to consult the documentation 
before deciding to develop your application based on **FF**.  
But if you do **FF** ensures a minimal amount of setup, letting you concentrate your efforts on your business logic
instead of some framework configuration.

# Dependencies

- **Fast Forward Family**

  **FF** makes heavy usage of the **Fast Forward Family** components, a collection of independent components providing
  generic implementations (like data structures of design patterns) used by many of **FF**'s features.

# Installation

## via Composer

## manual Installation

# Bootstrapping

## Bootstrap a web application

## Bootstrap a command line application

# Services and the Service Factory

## Services - a definition

From the **FF** perspective a service is a singular component (mostly class) that provide needed functionality as part
of certain domain. Common attributes of services should be
- Make minimal assumptions of the surrounding runtime environment.
- Be stateless.
- Be unit testable.

## Writing services

[**CONVENTION**] Service classes extend `FF\Services\AbstractService`.  
[**CONVENTION**] Services must be located in your project's `MyProject\Services` namespace (or any sub namespace 
thereof) to be found by the `ServicesFactory`.  

***Example: Basic service implementation***

    namespace MyProject\Services;

    use FF\Services\AbstractService;
    
    class MyService extends AbstractService
    {
    
    }
    
**FF** services can be configured if needed. The `ServicesFactory` will initialize a service's instance with available
config options. But it will be your responsibility to validate that any given options will meet your service's 
requirements.
    
***Example: Configurable service implementation***

    namespace MyProject\Services;

    use FF\Services\AbstractService;
    
    class MyConfigurableService extends AbstractService
    {
        /**
         * {@inheritDoc}
         */
        protected function validateOptions(array $options, array &$errors): bool
        {
            // place you option validation logic here
            // example
            if (!isset($options['some-option'])) {
                $errors[] = 'missing options [some-options]';
                return false;
            }
            
            return true;
        }
    }    

## Using the Service Factory

**FF**'s service factory is based on the `AbstractSingletonFactory` of the **FF-Factories** component. Be sure to 
consult component's [documentation](https://github.com/marcostoll/FF-Factories) for further information about using
**FF** factories.

To retrieve a service from the factory you have to know thw service's **class identifier**. These class identifiers are
composed of the service's class name prefixed by any sub namespace relative to `FF\Services` (for built-in services) or
`MyProject\Services` (for you custom services).

For convenience reasons there is a shorthand class `SF` that lets you retrieve one or more services with minimal effort.

***Example: Getting a single service***

    use FF\Factories\SF;
    use MyProject\Services\MyService;
    
    /** @var MyService $myService */
    $myService = SF::i()->get('MyService'); // finds MyProject\Services\MyService
    
***Example: Getting multiple services at once***    

    use FF\Factories\SF;
        use FF\Services\Events\EventBroker;
    use MyProject\Services\MyService;
    
    /** @var EventBroker $eventBroker */
    /** @var MyService $myService */
    list ($eventBroker, $myService) = SF::i()->get('Events\EventBroker', 'MyService');
     

## Extending built-in FF Services  

The `ServicesFactory` uses a `FF\Factories\NamespaceClassLocator` locator to find services definitions bases on a class
identifier. To archive this, it searches the list of registered base namespace for any suitable service class **in the 
given order**.

This feature lets you sub class and replace **FF**'s built-in service implementations easily by just following the 
naming conventions and registering the `ServiceFactory` as shown in the **Bootstrapping** section of this document.

***Example: Extend/Replace a built-in service***

    namespace MyProject\Services\Runtime;

    use FF\Services\Runtime\ExceptionHandler as FFExceptionHandler;
    
    class ExceptionHandler extends FFExceptionHandler
    {
        // place your custom logic here
    }
    
    ###############
    
    // when ever some component refers to the 'Runtime\ExceptionHandler' service via the ServicesFactory
    // an instance of your service extension will be used instead of the built-in service
    
    /** @var MyProject\Services\Runtime\ExceptionHandler $exceptionHandler */
    $exceptionHandler = SF::get('Runtime\ExceptionHandler');
    
# Events and the Event Broker

This feature provides a basic observer/observable pattern implementation. The key class is the `EventBroker`. Any
other class may act as an observable by firing events using the `EventBroker`'s api. Other classes my act as observers
by subscribing to specific types of events and being notified by the `EventBroker` in each time this type of event was
fired. 

## Firing Events

If an observable wants to notify potential observers of notable changes it simply fires a suitable event using the
`EventBroker`'s api.

***Example: Fire an event***

    use FF\Factories\SF;
    use FF\Services\Events\EventBroker;

    class MyExceptionHandler
    {
        /**
         * Generic exception handler callback
         *
         * @param \Throwable $e
         * @see http://php.net/set_exception_handler
         */
        public function onException(\Throwable $e)
        {
            try {
                /** @var EventBroker $eventBroker */
                $eventBroker = SF::i()->get('Events\EventBroker');
                $eventBroker->fire('Runtime\Exception', $e);
            } catch (\Exception $e) {
                // do not handle exceptions thrown while
                // processing the on-exception event
            }
        }
    }
    
The `fire` method of the `EventBroker` uses the `EventsFactory` to instantiate the actual event object passing any
additional argument provided to the event class's constructor.

## Subscribing to Events

A valid event handling method must meet the following requirements:

- must be public
- must not be static or abstract
- accept exactly one argument: the event classes instance

***Example: Subscribe to an event***

    use FF\Events;
    use FF\Factories\SF;
    use FF\Services\Events\EventBroker;    

    class MyErrorObserver
    {
        /**
         * Event handling callback
         *
         * @param Runtime\Error $event
        public function onRuntimeError(Runtime\Error $event)
        {
            // handle the Error event
            var_dump(
                $event->getErrNo(), 
                $event->getErrMsg(), 
                $event->getErrFile(),
                $event->getErrLine()
            );
        }
    }
    
    // subscribe to the Runtime\Error event
    /** @var EventBroker $eventBroker */
    $eventBroker = SF::i()->get('Events\EventBroker');
    $eventBroker->subscribe([new MyErrorObserver, 'onRuntimeError'], 'Runtime\Error');
        
The subscription is bases on the class identifier of the event class. This is exactly the same string to use by the
observable when firing the event. 

## Defining Custom Events   

[**CONVENTION**] Event classes extend `FF\Events\AbstractEvent`.  
[**CONVENTION**] Events must be located in your project's `MyProject\Events` namespace (or any sub namespace 
thereof) to be found by the `EventsFactory`.

***Example: A custom event***

    namespace MyProject\Events;
    
    use FF\Events\AbstractEvent;
    
    /**
     * This event's class identifier would just be 'Logoff'
     */
    class Logoff extends AbstractEvent
    {
        /**
         * Define constructor arguments (the event data) to meet your needs.
         */
        public function __construct($eventData)
        {
            var_dump($eventData);
        }
    }

# Road map

- Runtime
- Controllers
- Sessions
- Security
- CLI
- ORM
- Bootstrapping