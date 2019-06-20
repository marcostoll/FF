<?php
/**
 * Definition of ServiceLocatorTrait
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services;

/**
 * Trait ServiceLocatorTrait
 *
 * @package FF\Services
 */
trait ServiceLocatorTrait
{
    /**
     * Retrieves services from the factory
     *
     * @param string[] $classIdentifiers
     * @return AbstractService|AbstractService[]
     */
    protected function getService(string ...$classIdentifiers)
    {
        return SF::i()->get(...$classIdentifiers);
    }
}