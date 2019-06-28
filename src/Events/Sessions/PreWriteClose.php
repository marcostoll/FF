<?php
/**
 * Definition of PreWriteClose
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Sessions;

use FF\Events\AbstractEvent;
use FF\Services\Sessions\Session;

/**
 * Class PreWriteClose
 *
 * @package FF\Events\Sessions
 */
class PreWriteClose extends AbstractEvent
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }
}
