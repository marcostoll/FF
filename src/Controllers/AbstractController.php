<?php
/**
 * Definition of AbstractController
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Controllers;

use FF\Factories\SF;
use FF\Services\Dispatching\Dispatcher;
use FF\Services\Templating\TemplateRendererInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractController
 *
 * Concrete sub classes should define one or more action methods.
 *
 * Any action method must meet the following requirements:
 *      - must be public
 *      - must not be static
 *      - must return an instance of Symfony\Component\HttpFoundation\Response
 *
 * Action methods may define any number of arguments
 *
 * @package FF\Dispatching
 */
abstract class AbstractController
{
    /**
     * For use with the BaseNamespaceClassLocator of the ControllersFactory
     */
    const COMMON_NS_SUFFIX = 'Controllers';

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
     */
    protected function forward($controller, string $action, ...$args)
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = SF::i()->get('dispatching/dispatcher');

        return $dispatcher->forward($controller, $action, ...$args);
    }

    /**
     * Renders a template
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function render(string $template, array $data = []): string
    {
        return $this->getTemplateRenderer()->render($template, $data);
    }

    /**
     * @return TemplateRendererInterface
     */
    protected abstract function getTemplateRenderer(): TemplateRendererInterface;
}
