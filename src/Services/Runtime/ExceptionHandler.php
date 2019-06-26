<?php
/**
 * Definition of ExceptionHandler
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Runtime;

use FF\Services\AbstractService;

/**
 * Class ErrorHandler
 *
 * @package FF\Runtime
 */
class ExceptionHandler extends AbstractService implements RuntimeEventHandlerInterface
{
    /**
     * @var callable|null
     */
    protected $previousHandler;

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @see http://php.net/set_exception_handler
     */
    public function register()
    {
        $this->previousHandler = set_exception_handler([$this, 'onException']);
        return $this;
    }

    /**
     * Retrieves the previous exception handler callback before registration (if any)
     *
     * If register() wasn't called yet or no previous exception handler callback was registered, null will be returned.
     *
     * @return callable|null
     */
    public function getPreviousHandler(): ?callable
    {
        return $this->previousHandler;
    }

    /**
     * Restores the previous exception handler (if any)
     *
     * @return $this
     */
    public function restorePreviousHandler()
    {
        if (!is_callable($this->previousHandler)) {
            return $this;
        }

        set_exception_handler($this->previousHandler);
        $this->previousHandler = null;

        return $this;
    }

    /**
     * Generic exception handler callback
     *
     * To prevent potential loops any unhandled exception thrown while processing the Exception event
     * is caught and discarded.
     *
     * @param \Throwable $e
     * @fires Runtime\Exception
     * @see http://php.net/set_exception_handler
     */
    public function onException(\Throwable $e)
    {
        try {
            $this->fire('Runtime\Exception', $e);
        } catch (\Exception $e) {
            // do not handle exceptions thrown while
            // processing the Exception event
        }
    }
}
