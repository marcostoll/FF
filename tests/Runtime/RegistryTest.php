<?php
/**
 * Definition of RegistryTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Runtime;

use FF\Runtime\Registry;
use PHPUnit\Framework\TestCase;

/**
 * Test RegistryTest
 *
 * @package FF\Tests
 */
class RegistryTest extends TestCase
{
    /**
     * Tests the namesake method/feature
     */
    public function testGetInstance()
    {
        $instance = Registry::getInstance();
        $this->assertInstanceOf(Registry::class, $instance);
        $this->assertSame($instance, Registry::getInstance());
    }
}
