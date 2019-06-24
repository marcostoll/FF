<?php
/**
 * Definition of PostDispatch
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Dispatching;

use FF\Controllers\AbstractController;
use FF\Events\AbstractEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostDispatch
 *
 * @package FF\Events\Dispatching
 */
class PostDispatch extends AbstractEvent
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var AbstractController
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param Response $response
     * @param AbstractController $controller
     * @param string $action
     * @param array $args
     */
    public function __construct(Response $response, AbstractController $controller, $action, array $args = [])
    {
        $this->response = $response;
        $this->controller = $controller;
        $this->action = $action;
        $this->args = $args;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return AbstractController
     */
    public function getController(): AbstractController
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}
