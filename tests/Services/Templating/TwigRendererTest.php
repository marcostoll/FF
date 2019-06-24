<?php
/**
 * Definition of TwigRendererTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Services\Templating;

use FF\Events\AbstractEvent;
use FF\Events\Templating\PostRender;
use FF\Events\Templating\PreRender;
use FF\Factories\ServicesFactory;
use FF\Factories\SF;
use FF\Services\Events\EventBroker;
use FF\Services\Exceptions\ConfigurationException;
use FF\Services\Templating\Exceptions\RenderingException;
use FF\Services\Templating\TwigRenderer;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Test TwigRendererTest
 *
 * @package FF\Tests
 */
class TwigRendererTest extends TestCase
{
    const DEFAULT_OPTIONS = [
        'template-dir' => __DIR__ . '/templates'
    ];

    /**
     * @var TwigRenderer
     */
    protected $uut;

    /**
     * @var AbstractEvent[]
     */
    protected static $lastEvents;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        SF::setInstance(new ServicesFactory());

        /** @var EventBroker $eventBroker */
        $eventBroker = SF::i()->get('Events\EventBroker');

        // register test listener
        $eventBroker
            ->subscribe([__CLASS__, 'listener'], 'Templating\PreRender')
            ->subscribe([__CLASS__, 'listener'], 'Templating\PostRender');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->uut = new TwigRenderer(self::DEFAULT_OPTIONS);

        self::$lastEvents = [];
    }

    /**
     * Dummy event listener
     *
     * @param AbstractEvent $event
     */
    public static function listener(AbstractEvent $event)
    {
        self::$lastEvents[get_class($event)] = $event;
    }

    /**
     * Tests the namesake method/feature
     */
    public function testInitializeErrorMandatoryOptions()
    {
        $this->expectException(ConfigurationException::class);

        new TwigRenderer();
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSetGetTwig()
    {
        $value = new Environment(new ArrayLoader());
        $same = $this->uut->setTwig($value);
        $this->assertSame($this->uut, $same);
        $this->assertEquals($value, $this->uut->getTwig());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testSetGetFireEvents()
    {
        $value = false;
        $same = $this->uut->setFireEvents($value);
        $this->assertSame($this->uut, $same);
        $this->assertEquals($value, $this->uut->getFireEvents());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRenderWithoutEvents()
    {
        $doc = $this->uut->setFireEvents(false)
            ->render('basic.html.twig', ['foo' => 'bar']);
        $this->assertEquals('foo: bar', $doc);

        $this->assertEmpty(self::$lastEvents);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRenderWithEvents()
    {
        $doc = $this->uut->setFireEvents(true)
            ->render('basic.html.twig', ['foo' => 'bar']);
        $this->assertEquals('foo: bar', $doc);

        $this->assertArrayHasKey(PreRender::class, self::$lastEvents);
        $this->assertArrayHasKey(PostRender::class, self::$lastEvents);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRenderErrorLoader()
    {
        $this->expectException(RenderingException::class);

        $this->uut->render('missing.html.twig', []);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testRenderErrorSyntax()
    {
        $this->expectException(RenderingException::class);

        $this->uut->render('invalid.html.twig', []);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testMagicCall()
    {
        $this->uut->addGlobal('foo', 'bar');

        $doc = $this->uut->render('basic.html.twig', []);
        $this->assertEquals('foo: bar', $doc);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testMagicCallUnknown()
    {
        $this->expectException(Error::class);

        $this->uut->foo();
    }
}