<?php
/**
 * Definition of EventsFactoryTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Factories;

use FF\Factories\ClassLocators\BaseNamespaceClassLocator;
use FF\Factories\EventsFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test EventsFactoryTest
 *
 * @package FF\Tests
 */
class EventsFactoryTest extends TestCase
{
    /**
     * Tests the namesake method/feature
     */
    public function testGetInstance()
    {
        $instance = EventsFactory::getInstance();
        $this->assertInstanceOf(EventsFactory::class, $instance);
        $this->assertSame($instance, EventsFactory::getInstance());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetClassLocator()
    {
        $this->assertInstanceOf(BaseNamespaceClassLocator::class, EventsFactory::getInstance()->getClassLocator());
    }
}