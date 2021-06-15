<?php
/**
 * Definition of Session
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Sessions;

use FF\Factories\Exceptions\ClassNotFoundException;
use FF\Factories\SF;
use FF\Services\AbstractService;
use FF\Services\Exceptions\ConfigurationException;
use FF\Sessions\Session\Exceptions\SessionException;

/**
 * Class EventBroker
 *
 * Options:
 *
 * Accepts all options defined by the php built-in session handling.
 *
 * Defines the following defaults differing from php's built-in default session configuration (mostly for security
 * reasons):
 *  - session.use_strict_mode : bool (default: true)    - whether the module will use strict session id mode
 *  - session.cookie_httponly : bool (default: true)    - marks the cookie as accessible only through the HTTP protocol
 *
 * Additional options:
 *
 *  - custom_handler : string (default: '')             - class identifier of a valid service class implementing the
 *                                                        SessionHandlerInterface.
 *                                                        Must be retrievable vie the service factory using the class
 *                                                        identifier
 *  - fire-events   : bool (default: false)             - whether to fire session events
 *
 * Recommendations:
 *
 * Set a unique session name for your project:
 *  - session.name  : string (default: 'PHPSESSID')     - name of the session cookie,
 *                                                        should only contain alphanumeric characters
 *
 * If your project solely uses a secure (SSL) https web interface, you should active the secure cookie option:
 *  - session.cookie_secure  : bool (default: false)    - whether cookies should only be sent over secure connection
 *
 * Do not set session.auto_start. Always call Session::start() explicitly on your demand.
 *  - session.auto_start    : bool (default: false)     - whether the session module starts a session automatically
 *
 * @package FF\Services\Sessions
 *
 * @link https://www.php.net/manual/en/session.configuration.php
 */
class Session extends AbstractService
{
    const ENHANCED_CONFIG_DEFAULTS = [
        'session.use_strict_mode' => '1',
        'session.cookie_httponly' => '1'
    ];

    /**
     * Retrieves the session name
     *
     * @return string
     * @see http://php.net/session_name
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Retrieves the session id
     *
     * @return string
     * @see http://php.net/session_id
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Retrieves the current session's status
     *
     * @return int
     * @see http://php.net/session_status
     */
    public function getStatus(): int
    {
        return session_status();
    }

    /**
     * Checks whether an active session exists
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return ($this->getStatus() == PHP_SESSION_ACTIVE);
    }

    /**
     * Registers a session save handler
     *
     * Does nothing if session is already active.
     *
     * @param \SessionHandlerInterface $sessionHandler
     * @param bool $registerShutdown
     * @return $this
     * @throws SessionException error while registering session save handler
     * @see http://php.net/session_set_save_handler
     */
    public function setSaveHandler(\SessionHandlerInterface $sessionHandler, $registerShutdown = true)
    {
        if ($this->isActive()) {
            return $this;
        }

        $success = session_set_save_handler($sessionHandler, $registerShutdown);
        if (!$success) {
            throw new SessionException(
                'error while registering session save handler',
                SessionException::ERROR_SESSION_SAVE_HANDLER
            );
        }

        return $this;
    }

    /**
     * Starts the session
     *
     * Does nothing if session is already active.
     *
     * You may provide specialized $options that will override this service's options.
     * If you will, do not use the 'session.' prefix with the keys of the $options array.
     *
     * @param array $options
     * @return $this
     * @throws SessionException error while starting session
     * @fires Sessions\PreStart
     * @fires Sessions\PostStart
     * @see http://php.net/session_start
     */
    public function start(array $options = [])
    {
        if ($this->isActive()) {
            return $this;
        }

        $this->fire('Sessions\PreStart');

        $success = session_start($options);
        if (!$success) {
            throw new SessionException('error while starting session', SessionException::ERROR_SESSION_START);
        }

        $this->fire('Sessions\PostStart', $this);

        return $this;
    }

    /**
     * Writes and closes any active session
     *
     * Does nothing id the session has not been started yet.
     *
     * @return $this
     * @fires Sessions\PreWriteClose
     * @see http://php.net/session_write_close
     */
    public function writeClose()
    {
        if (!$this->isActive()) {
            return $this;
        }

        $this->fire('Sessions\PreWriteClose', $this);

        session_write_close();
        return $this;
    }

    /**
     * Regenerates the session id
     *
     * Does nothing id the session has not been started yet.
     *
     * @param boolean $deleteOldSession
     * @return $this
     * @throws SessionException error while regenerating session id
     * @see http://php.net/session_regenerate_id
     */
    public function regenerate($deleteOldSession = true)
    {
        if (!$this->isActive()) {
            return $this;
        }

        $success = session_regenerate_id($deleteOldSession);
        if (!$success) {
            throw new SessionException(
                'error while regenerating session id',
                SessionException::ERROR_SESSION_REGENERATE
            );
        }

        return $this;
    }

    /**
     * Destroys the current session
     *
     * Clears all values from the session and marks it as outdated first.
     *
     * @return $this
     * @throws SessionException error while destroying the session
     * @fires Sessions\PreDestroy
     * @see http://php.net/session_destroy
     */
    public function destroy()
    {
        $this->fire('Sessions\PreDestroy', $this);

        $this->unset()->markOutdated();

        $success = session_destroy();
        if (!$success) {
            throw new SessionException(
                'error while destroying the session',
                SessionException::ERROR_SESSION_DESTROY
            );
        }

        return $this;
    }

    /**
     * Sends a session cookie with an outdated expiration date
     *
     * Does nothing if session doesn't use cookies
     *
     * @return $this
     */
    public function markOutdated()
    {
        // send outdated session cookie
        if (!ini_get('session.use_cookies')) {
            return $this;
        }

        $params = session_get_cookie_params();
        setcookie(
            $this->getName(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );

        return $this;
    }

    /**
     * Retrieves a stored session value
     *
     * If no value is present using $key, null is returned.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Sets a session value
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Checks whether a certain session value is set
     *
     * Keep in mind, that null values will be treated is unset.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Clears session values
     *
     * If $key is set, only the value using the $key is unset.
     *
     * @param string $key
     * @return $this
     * @see http://php.net/session_unset
     */
    public function unset(string $key = null)
    {
        if (is_null($key)) {
            session_unset();
            $_SESSION = [];
            return $this;
        }

        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(array $options)
    {
        // merge own defaults
        $options = array_merge(self::ENHANCED_CONFIG_DEFAULTS, $options);
        parent::initialize($options);

        $this->fireEvents = $this->getOption('fire-events', false);

        $errors = [];
        $this->configureSessionModule($options, $errors);

        // initialize session handler (if any)
        if (!isset($options['custom_handler'])) {
            return;
        }

        try {
            $sessionHandler = SF::i()->get($options['custom_handler']);
            if (!($sessionHandler instanceof \SessionHandlerInterface)) {
                $errors[] = '[' . $options['custom_handler'] . '] is not a \SessionHandlerInterface';
            }

            $this->setSaveHandler($sessionHandler);
        } catch (ClassNotFoundException | ConfigurationException $exception) {
            $errors[] = 'unable to retrieve [' . $options['custom_handler'] . '] from the service factory - '
                . '[' . (string)$exception . ']';
        }

        if (!empty($errors)) {
            throw new ConfigurationException($errors);
        }
    }

    /**
     * Configure php's session module
     *
     * @param array $options
     * @param array $errors
     */
    protected function configureSessionModule(array $options, array $errors)
    {
        foreach ($options as $key => $value) {
            if (substr($key, 0, 8) != 'session.') {
                continue;
            }

            $result = ini_set($key, (string)$value);
            if ($result === false) {
                $errors[] = 'error while processing option [' . $key . ']';
            }
        }
    }
}
