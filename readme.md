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
1. Services and a Service Factory

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
[**CONVENTION**] Services should be located in your project's `MyProject\Services` namespace (or any sub namespace 
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

    use FF\Services\SF;
    use MyProject\Services\MyService;
    
    /** @var MyService $myService */
    $myService = SF::i()->get('MyService'); // finds MyProject\Services\MyService
    
***Example: Getting multiple services at once***    

    use FF\Services\Events\EventBroker;
    use FF\Services\SF;
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
    

# Road map

- Events
- Runtime
- Controllers
- Sessions
- Security
- CLI
- ORM
- Bootstrapping