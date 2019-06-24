<?php
/**
 * Definition of Dispatcher
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Dispatching;

use FF\Controllers\AbstractController;
use FF\Factories\ControllersFactory;
use FF\Factories\Exceptions\ClassNotFoundException;
use FF\Services\AbstractService;
use FF\Services\Dispatching\Exceptions\ControllerInspectionException;
use FF\Services\Dispatching\Exceptions\IncompleteRouteException;
use FF\Services\Exceptions\ResourceNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException as SymfonyResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Dispatcher
 *
 * Options:
 *
 *  - routing-yaml  : string                    - path to routing yaml
 *  - fire-events   : bool (default: false)     - whether to fire rendering events
 *
 * @package FF\Services\Dispatching
 */
class Dispatcher extends AbstractService
{
    const RESERVED_ROUTE_PARAMS = ['controller', 'action', '_route'];

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var bool
     */
    protected $fireEvents;

    /**
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @param RouteCollection $routes
     * @return $this
     */
    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFireEvents(): bool
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
     * Retrieves a route's path by name
     *
     * @param string $name
     * @return string
     */
    public function getRoutePath(string $name): string
    {
        $route = $this->routes->get($name);
        if (is_null($route)) return '';

        $path = $route->getPath();
        return !empty($path) ? $path : '/';
    }

    /**
     * Builds a relative path
     *
     * Strips non-filled path tokens from the end of the path.
     * Returns an empty string if $routeName is not found.
     *
     * @param string $routeName
     * @param array $namedArgs
     * @return string
     */
    public function buildPath(string $routeName, array $namedArgs = []): string
    {
        $route = $this->routes->get($routeName);
        if (is_null($route)) return '';

        // add omitted args having defaults in route's definition
        foreach ($route->getDefaults() as $name => $default) {
            if ($name == 'controller' || $name == 'action') continue;
            if (array_key_exists($name, $namedArgs)) continue;

            $namedArgs[$name] = $default;
        }

        // fill-in args in route's path
        $path = $route->getPath();
        foreach ($namedArgs as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        // strip unfilled args from end of path
        // e.g. /something/foo/{bar}
        $path = preg_replace('~(/{[^}]+})+$~', '', $path);

        if (empty($path)) $path = '/';

        return $path;
    }


    /**
     * Retrieves parameters of a matching route for the request given
     *
     * @param Request $request
     * @return array|null
     * @throws IncompleteRouteException
     */
    public function match(Request $request): ?array
    {
        $context = new RequestContext();
        $context->fromRequest($request);

        try {
            $matcher = new UrlMatcher($this->routes, $context);
            $pathInfo = $context->getPathInfo();
            $parameters = $matcher->match($pathInfo);
            if (!isset($parameters['controller'])) {
                throw new IncompleteRouteException(
                    'controller param missing from route [' . $parameters['_route'] . ']'
                );
            }
            if (!isset($parameters['action'])) {
                throw new IncompleteRouteException('action param missing from route [' . $parameters['_route'] . ']');
            }

            return $parameters;
        } catch (SymfonyResourceNotFoundException $e) {
            return null;
        }
    }

    /**
     * Dispatches a request
     *
     * @param Request $request
     * @return Response
     * @throws ResourceNotFoundException no route found for request
     * @fires Dispatching\PreDispatch
     * @fires Dispatching\PostRoute
     * @fires Dispatching\PostDispatch
     */
    public function dispatch(Request $request): Response
    {
        if ($this->fireEvents) {
            $this->fire('Dispatching\PreDispatch', $request);
        }

        $parameters = $this->match($request);
        if (is_null($parameters)) {
            throw new ResourceNotFoundException('no route found for request [' . $request->getPathInfo() . ']');
        }

        try {
            $controller = ControllersFactory::getInstance()->create($parameters['controller']);
            $action = $parameters['action'];
            $args = $this->extractArgs($parameters);
            $actionArgs = $this->buildActionArgs($controller, $action, $args);
        } catch (ClassNotFoundException $e) {
            throw new ResourceNotFoundException('controller [' . $parameters['controller'] . '] not found', 0, $e);
        } catch(ControllerInspectionException $e) {
            throw new ResourceNotFoundException(
                'action [' . $parameters['action'] . '] not found in controller [' . $parameters['controller'] . ']',
                0,
                $e
            );
        }

        if ($this->fireEvents) {
            $this->fire('Dispatching\PostRoute', $request, $controller, $action, $args);
        }

        /** @var Response $response */
        $response = call_user_func_array([$controller, $action], $actionArgs);

        if ($this->fireEvents) {
            $this->fire('Dispatching\PostDispatch', $response, $controller, $action, $actionArgs);
        }

        return $response;
    }

    /**
     * Forwards to another controller action
     *
     * This method can be invoked with an arbitrary amount of arguments.
     * Any $args will be passed to the designated forwarded action in the given order.
     *
     * @param AbstractController|string $controller A controller instance or the class identifier of a controller class
     * @param string $action
     * @param array $args
     * @return Response
     * @throws \InvalidArgumentException action not callable
     * @fires Dispatching\PreForward
     */
    public function forward($controller, string $action, ...$args)
    {
        if (is_string($controller)) {
            $controller = ControllersFactory::getInstance()->create($controller);
        }

        if ($this->fireEvents) {
            $this->fire('Dispatching\PreForward', $controller, $action, $args);
        }

        if (!method_exists($controller, $action) || !is_callable([$controller, $action])) {
            throw new \InvalidArgumentException(
                'controller [' . get_class($controller) . '] does not define a callable action [' . $action . ']'
            );
        }

        // invoke forwarded action
        $methodArgs = $this->buildActionArgs($controller, $action, $args);
        /** @var Response $response */
        $response = call_user_func_array([$controller, $action], $methodArgs);

        return $response;
    }

    /**
     * Retrieves the action arguments
     *
     * @param array $parameters
     * @return array
     */
    protected function extractArgs(array $parameters): array
    {
        $args = [];
        foreach ($parameters as $key => $value) {
            if (in_array($key, self::RESERVED_ROUTE_PARAMS)) continue;
            $args[$key] = $value;
        }

        return $args;
    }

    /**
     * Builds the arguments list for invoking the desired action
     *
     * @param AbstractController $controller
     * @param string $action
     * @param array $args
     * @return array
     * @throws ResourceNotFoundException
     * @throws ControllerInspectionException
     */
    protected function buildActionArgs(AbstractController $controller, string $action, array $args): array
    {
        $methodArgs = [];
        try {
            $reflection = new \ReflectionMethod($controller, $action);
            foreach ($reflection->getParameters() as $param) {
                $name = $param->getName();
                if (!isset($args[$name])) {
                    if (!$param->isOptional()) {
                        throw new ResourceNotFoundException(
                            'missing required argument [' . $name . '] for action [' . $action . '] '
                            . 'of controller [' . get_class($controller) . ']'
                        );
                    }
                    $methodArgs[] = $param->getDefaultValue();
                } else {
                    $methodArgs[] = $args[$name];
                }
            }
        } catch (\ReflectionException $e) {
            throw new ControllerInspectionException(
                'error while inspecting action [' . $action . '] of controller [' . get_class($controller) . ']',
                0,
                $e
            );
        }

        return $methodArgs;
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(array $options)
    {
        parent::initialize($options);

        $this->fireEvents = $this->getOption('fire-events', false);

        $locator = new FileLocator(dirname($this->getOption('routing-yaml')));
        $loader = new YamlFileLoader($locator);
        $this->routes = $loader->load(basename($this->getOption('routing-yaml')));
    }

    /**
     * {@inheritDoc}
     */
    protected function validateOptions(array $options, array &$errors): bool
    {
        if (!isset($options['routing-yaml']) || empty($options['routing-yaml'])) {
            $errors[] = 'missing or empty mandatory option [routing-yaml]';
            return false;
        }

        return true;
    }
}
