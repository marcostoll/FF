<?php
/**
 * Class ConfigurationException
 *
 * @package FF\Services\Exceptions
 * @author Marco Stoll <m.stoll@core4.de>
 * @link http://core4.de CORE4 GmbH & Co. KG
 * @filesource
 */

namespace FF\Services\Exceptions;

/**
 * Class ConfigurationException
 */
class ConfigurationException extends \RuntimeException
{
    /**
     * @param string[] $errors
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(array $errors, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(implode(PHP_EOL, $errors), $code, $previous);
    }
}
