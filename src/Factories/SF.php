<?php
/**
 * Definition of SF
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Factories;

/**
 * Class SF
 *
 * @package FF\Services
 */
class SF extends ServicesFactory
{
    /**
     * Retrieves the ServicesFactory's singleton instance
     *
     * @return ServicesFactory
     */
    public static function i(): ServicesFactory
    {
        return parent::getInstance();
    }
}
