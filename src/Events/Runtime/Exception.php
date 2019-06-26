<?php
/**
 * Definition of Exception
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events\Runtime;

use FF\Events\AbstractEvent;

/**
 * Class Exception
 *
 * @package FF\Events\Runtime
 */
class Exception extends AbstractEvent
{
    /**
     * @var \Throwable
     */
    protected $exception;

    /**
     * @param \Throwable $exception
     */
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
