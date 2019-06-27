<?php
/**
 * Definition of ShutdownHandlerTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Services\Runtime;

use FF\Events\Runtime\Shutdown;
use FF\Factories\ServicesFactory;
use FF\Factories\SF;
use FF\Services\Events\EventBroker;
use FF\Services\Runtime\ShutdownHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test ShutdownHandlerTest
 *
 * @package FF\Tests
 */
class ShutdownHandlerTest extends TestCase
{
    /**
     * @var ShutdownHandler
     */
    protected $uut;

    /**
     * @var Shutdown
     */
    protected static $lastEvent;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        SF::setInstance(new ServicesFactory());

        /** @var EventBroker $eventBroker */
        $eventBroker = SF::i()->get('Events\EventBroker');

        // register test listener
        $eventBroker->subscribe([__CLASS__, 'listener'], 'Runtime\Shutdown');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->uut = new ShutdownHandler();
        self::$lastEvent = null;
    }

    /**
     * @param Shutdown $event
     */
    public static function listener(Shutdown $event)
    {
        self::$lastEvent = $event;
    }

    /**
     * Dummy callback
     */
    public function dummyHandler()
    {
        $this->fail('dummy handler should never be called');
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSetHasForceExit()
    {
        $same = $this->uut->setForceExit(false);
        $this->assertSame($this->uut, $same);
        $this->assertFalse($this->uut->hasForceExit());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRegister()
    {
        $same = $this->uut->register();
        $this->assertSame($this->uut, $same);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testTriggerShutdownHandling()
    {
        $this->uut->register()
            ->onShutdown();

        $this->assertNotNull(self::$lastEvent);
    }
}
