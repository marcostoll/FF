<?php
/**
 * Definition of ShutdownHandler
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
 * Options:
 *
 *  - force-exit    : bool (default: false)     - invoke exit() after firing Shutdown event
 *
 * @package FF\Services\Runtime
 */
class ShutdownHandler extends AbstractService implements RuntimeEventHandlerInterface
{
    /**
     * List of codes indicating fatal errors
     */
    const FATAL_ERRORS = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_RECOVERABLE_ERROR
    ];

    /**
     * @var bool
     */
    protected $forceExit;

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @see http://php.net/register_shutdown_function
     */
    public function register()
    {
        register_shutdown_function([$this, 'onShutdown']);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasForceExit(): bool
    {
        return $this->forceExit;
    }

    /**
     * @param bool $forceExit
     * @return $this
     */
    public function setForceExit(bool $forceExit)
    {
        $this->forceExit = $forceExit;
        return $this;
    }

    /**
     * Generic shutdown handler callback
     *
     * Terminates further execution via exit() if configured to do so,
     * thus stopping any additional shutdown handlers from being called.
     *
     * Fires an additional OnError event, in case a fatal error is detected
     *
     * @fires Runtime\Shutdown
     * @fires Runtime\Error
     * @see http://php.net/register_shutdown_function
     * @see http://php.net/error_get_last
     */
    public function onShutdown()
    {
        $error = error_get_last();
        if (!is_null($error) && in_array($error['type'], self::FATAL_ERRORS)) {
            $this->fire(
                'Runtime\Error',
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }

        $this->fire('Runtime\Shutdown');

        if ($this->forceExit) {
            exit();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(array $options)
    {
        parent::initialize($options);

        $this->forceExit = $this->getOption('force-exit', false);
    }
}
