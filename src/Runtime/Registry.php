<?php
/**
 * Definition of Registry
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Runtime;

use FF\DataStructures\Record;

/**
 * Class Registry
 *
 * @package FF\Runtime
 */
class Registry extends Record
{
    /**
     * @var Registry
     */
    protected static $instance;

    /**
     * Retrieves the singleton instance of this class
     *
     * return Registry
     */
    public static function getInstance(): Registry
    {
        if (is_null(self::$instance)) {
            self::$instance = new Registry();
        }

        return self::$instance;
    }
}
