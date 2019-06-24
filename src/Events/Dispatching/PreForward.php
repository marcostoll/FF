<?php
/**
 * Definition of PreForward
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Dispatching;

use FF\Controllers\AbstractController;
use FF\Events\AbstractEvent;

/**
 * Class PreForward
 *
 * @package FF\Events\Dispatching
 */
class PreForward extends AbstractEvent
{
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
    protected $args = [];

    /**
     * @param AbstractController $controller
     * @param string $action
     * @param array $args
     */
    public function __construct(AbstractController $controller, string $action, array $args = [])
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->args = $args;
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