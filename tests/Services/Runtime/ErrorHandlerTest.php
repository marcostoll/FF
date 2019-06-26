<?php
/**
 * Definition of ErrorHandlerTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Services\Runtime;

use FF\Events\Runtime\Error;
use FF\Factories\ServicesFactory;
use FF\Factories\SF;
use FF\Services\Events\EventBroker;
use FF\Services\Runtime\ErrorHandler;
use PHPUnit\Framework\TestCase;

/**
 * Test ErrorHandlerTest
 *
 * @package FF\Tests
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * @var mixed
     */
    protected static $currentHandler;

    /**
     * @var ErrorHandler
     */
    protected $uut;

    /**
     * @var Error
     */
    protected static $lastEvent;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        // store current handler
        self::$currentHandler = set_error_handler(null);

        SF::setInstance(new ServicesFactory());

        /** @var EventBroker $eventBroker */
        $eventBroker = SF::i()->get('Events\EventBroker');

        // register test listener
        $eventBroker->subscribe([__CLASS__, 'listener'], 'Runtime\Error');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        // unregister all error handlers
        while (true) {
            if (is_null(set_error_handler(null))) {
                break;
            }
        }

        $this->uut = new ErrorHandler();
        self::$lastEvent = null;
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        // unregister all error handlers
        while (true) {
            if (is_null(set_error_handler(null))) {
                break;
            }
        }

        // re-register original error handler (if any)
        set_error_handler(self::$currentHandler);
    }

    /**
     * @param Error $event
     */
    public static function listener(Error $event)
    {
        self::$lastEvent = $event;
    }

    /**
     * Dummy callback
     *
     * @param int $errNo
     * @param string $errMsg
     * @param string $errFile
     * @param int $errLine
     * @param array $errContext
     * @return boolean
     */
    public function dummyHandler(
        int $errNo,
        string $errMsg,
        string $errFile = '',
        int $errLine = null,
        array $errContext = []
    ): bool {
        $this->fail('dummy handler should never be called [' . serialize(func_get_args()) . ']');
        return false;
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSetGetErrorTypes()
    {
        $value = E_ERROR;
        $same = $this->uut->setErrorTypes($value);
        $this->assertSame($this->uut, $same);
        $this->assertEquals($value, $this->uut->getErrorTypes());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSetGetBypassPhpErrorHandling()
    {
        $same = $this->uut->setBypassPhpErrorHandling(false);
        $this->assertSame($this->uut, $same);
        $this->assertFalse($this->uut->getBypassPhpErrorHandling());
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
        $uutHandler = set_error_handler([$this, 'dummyHandler']);
        $this->assertSame([$this->uut, 'onError'], $uutHandler);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRegisterWithPrevious()
    {
        $callback = [$this, 'dummyHandler'];
        set_error_handler($callback);

        $this->uut->register();
        $this->assertSame($callback, $this->uut->getPreviousHandler());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRestorePreviousHandler()
    {
        $callback = [$this, 'dummyHandler'];
        set_error_handler($callback);

        $same = $this->uut->register()->restorePreviousHandler();
        $this->assertSame($same, $this->uut);
        $this->assertNull($this->uut->getPreviousHandler());

        $previous = set_error_handler(null);
        $this->assertSame($callback, $previous);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testTriggerErrorHandling()
    {
        $msg = 'testing ErrorHandler';
        $this->uut->register()
            ->onError(E_NOTICE, $msg);

        $this->assertNotNull(self::$lastEvent);
        $this->assertEquals(E_NOTICE, self::$lastEvent->getErrNo());
        $this->assertEquals($msg, self::$lastEvent->getErrMsg());
    }
}
