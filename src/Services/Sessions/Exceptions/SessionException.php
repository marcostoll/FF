<?php
/**
 * Class SessionException
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */

namespace FF\Sessions\Session\Exceptions;

/**
 * Class SessionException
 *
 * @package FF\Services\Sessions\Exceptions
 */
class SessionException extends \RuntimeException
{
    const ERROR_SESSION_START = 1;
    const ERROR_SESSION_DESTROY = 2;
    const ERROR_SESSION_REGENERATE = 3;
    const ERROR_SESSION_SAVE_HANDLER = 4;
}
