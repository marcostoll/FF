<?php
/**
 * Definition of AbstractEvent
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Events;

use FF\Factories\ClassLocators\ClassIdentifierAwareInterface;

/**
 * Class AbstractEvent
 *
 * @package FF\Events
 */
abstract class AbstractEvent implements ClassIdentifierAwareInterface
{
    /**
     * For use with the BaseNamespaceClassLocator of the EventsFactory
     */
    const COMMON_NS_SUFFIX = 'Events';

    /**
     * @var bool
     */
    protected $isCanceled = false;

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->isCanceled;
    }

    /**
     * @param bool $isCanceled
     * @return $this
     */
    public function setIsCanceled(bool $isCanceled)
    {
        $this->isCanceled = $isCanceled;
        return $this;
    }

    /**
     * @return $this
     */
    public function cancel()
    {
        return $this->setIsCanceled(true);
    }

    /**
     * {@inheritDoc}
     */
    public static function getClassIdentifier(): string
    {
        $className = get_called_class();
        $needle = '\\' . self::COMMON_NS_SUFFIX . '\\';
        $pos = strpos($className, $needle);
        if ($pos === false) return $className;

        return substr($className, $pos + strlen($needle));
    }
}