<?php
/**
 * Definition of ControllersFactoryTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Factories;

use FF\Factories\ClassLocators\BaseNamespaceClassLocator;
use FF\Factories\ControllersFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test ControllersFactoryTest
 *
 * @package FF\Tests
 */
class ControllersFactoryTest extends TestCase
{
    /**
     * Tests the namesake method/feature
     */
    public function testGetInstance()
    {
        $instance = ControllersFactory::getInstance();
        $this->assertInstanceOf(ControllersFactory::class, $instance);
        $this->assertSame($instance, ControllersFactory::getInstance());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetClassLocator()
    {
        $this->assertInstanceOf(BaseNamespaceClassLocator::class, ControllersFactory::getInstance()->getClassLocator());
    }
}
