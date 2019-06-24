<?php
/**
 * Definition of PreDispatch
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Dispatching;

use FF\Events\AbstractEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PreDispatch
 *
 * @package FF\Events\Dispatching
 */
class PreDispatch extends AbstractEvent
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Retrieves the request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
