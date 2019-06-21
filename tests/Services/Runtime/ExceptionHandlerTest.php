<?php
/**
 * Definition of ExceptionHandlerTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Runtime;

use FF\Events\Runtime\Exception;
use FF\Factories\ServicesFactory;
use FF\Factories\SF;
use FF\Services\Events\EventBroker;
use FF\Services\Runtime\ExceptionHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test ExceptionHandlerTest
 *
 * @package FF\Tests
 */
class ExceptionHandlerTest extends TestCase
{
    /**
     * @var mixed
     */
    protected static $currentHandler;

    /**
     * @var ExceptionHandler
     */
    protected $uut;

    /**
     * @var Exception
     */
    protected static $lastEvent;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        // store current handler
        self::$currentHandler = set_exception_handler(null);

        SF::setInstance(new ServicesFactory());

        /** @var EventBroker $eventBroker */
        $eventBroker = SF::i()->get('Events\EventBroker');

        // register test listener
        $eventBroker->subscribe([__CLASS__, 'listener'], 'Runtime\Exception');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        // unregister all error handlers
        while (true) {
            if (is_null(set_exception_handler(null))) break;
        }

        $this->uut = new ExceptionHandler();
        self::$lastEvent = null;
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        // unregister all error handlers
        while (true) {
            if (is_null(set_exception_handler(null))) break;
        }

        // re-register original error handler (if any)
        set_exception_handler(self::$currentHandler);
    }

    /**
     * @param Exception $event
     */
    public static function listener(Exception $event)
    {
        self::$lastEvent = $event;
    }

    /**
     * Dummy handler callback
     *
     * @param \Throwable $e
     */
    public function dummyHandler(\Throwable $e)
    {
        $this->fail('dummy handler should never be called [' . serialize(func_get_args()) . ']');
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRegister()
    {
        $same = $this->uut->register();
        $this->assertSame($this->uut, $same);

        // register another error handler on top
        // uut error handler should be found as the previous one
        $uutHandler = set_exception_handler([$this, 'dummyHandler']);
        $this->assertSame([$this->uut, 'onException'], $uutHandler);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRegisterWithPrevious()
    {
        $callback = [$this, 'dummyHandler'];
        set_exception_handler($callback);

        $this->uut->register();
        $this->assertSame($callback, $this->uut->getPreviousHandler());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testTriggerExceptionHandling()
    {
        $e = new \Exception('testing ExceptionHandler');
        $this->uut->register()
            ->onException($e);

        $this->assertNotNull(self::$lastEvent);
        $this->assertSame($e, self::$lastEvent->getException());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRestorePreviousHandler()
    {
        $callback = [$this, 'dummyHandler'];
        set_exception_handler($callback);

        $same = $this->uut->register()->restorePreviousHandler();
        $this->assertSame($same, $this->uut);
        $this->assertNull($this->uut->getPreviousHandler());

        $previous = set_exception_handler(null);
        $this->assertSame($callback, $previous);
    }
}