<?php
/**
 * Definition of SFTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Services;

use FF\Services\ServicesFactory;
use FF\Services\SF;
use PHPUnit\Framework\TestCase;

/**
 * Test SFTest
 *
 * @package FF\Tests
 */
class SFTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        $servicesFactory = new ServicesFactory();

        SF::setInstance($servicesFactory);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testI()
    {
        $sf = SF::i();
        $this->assertInstanceOf(ServicesFactory::class, $sf);
    }
}