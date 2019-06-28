<?php
/**
 * Definition of DispatcherTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Services\Dispatching {

    use FF\Events\AbstractEvent;
    use FF\Events\Dispatching\PostDispatch;
    use FF\Events\Dispatching\PostRoute;
    use FF\Events\Dispatching\PreDispatch;
    use FF\Events\Dispatching\PreForward;
    use FF\Factories\ControllersFactory;
    use FF\Factories\Exceptions\ClassNotFoundException;
    use FF\Factories\ServicesFactory;
    use FF\Factories\SF;
    use FF\Services\Dispatching\Dispatcher;
    use FF\Services\Dispatching\Exceptions\IncompleteRouteException;
    use FF\Services\Events\EventBroker;
    use FF\Services\Exceptions\ConfigurationException;
    use FF\Services\Exceptions\ResourceNotFoundException;
    use FF\Tests\Services\Dispatching\Controllers\HelloWorldController;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\RouteCollection;

    /**
     * Test DispatcherTest
     *
     * @package FF\Tests
     */
    class DispatcherTest extends TestCase
    {
        const DEFAULT_OPTIONS = [
            'routing-yaml' => __DIR__ . '/routing/test-routing.yml'
        ];

        /**
         * @var AbstractEvent[]
         */
        protected static $lastEvents;

        /**
         * @var Dispatcher
         */
        protected $uut;

        /**
         * {@inheritdoc}
         */
        public static function setUpBeforeClass(): void
        {
            SF::setInstance(new ServicesFactory());

            ControllersFactory::getInstance()
                ->getClassLocator()
                ->prependNamespaces(__NAMESPACE__);

            /** @var EventBroker $eventBroker */
            $eventBroker = SF::i()->get('Events\EventBroker');

            // register test listener
            $eventBroker
                ->subscribe([__CLASS__, 'listener'], 'Dispatching\PreDispatch')
                ->subscribe([__CLASS__, 'listener'], 'Dispatching\PostRoute')
                ->subscribe([__CLASS__, 'listener'], 'Dispatching\PostDispatch')
                ->subscribe([__CLASS__, 'listener'], 'Dispatching\PreForward');
        }

        /**
         * {@inheritdoc}
         */
        protected function setUp(): void
        {
            $this->uut = new Dispatcher(self::DEFAULT_OPTIONS);

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
         * Retrieves a request instance
         *
         * @param string $requestUri
         * @return Request
         */
        protected function buildRequest($requestUri)
        {
            return new Request([], [], [], [], [], ['REQUEST_URI' => $requestUri]);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testInitializeErrorMandatoryOptions()
        {
            $this->expectException(ConfigurationException::class);

            new Dispatcher();
        }

        /**
         * Tests the namesake method/feature
         */
        public function testSetGetRoutes()
        {
            $value = new RouteCollection();
            $same = $this->uut->setRoutes($value);
            $this->assertSame($this->uut, $same);
            $this->assertSame($value, $this->uut->getRoutes());
        }

        /**
         * Tests the namesake method/feature
         */
        public function testGetRoutePath()
        {
            $this->assertEquals('/default', $this->uut->getRoutePath('default'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testGetRoutePathMissing()
        {
            $this->assertEquals('', $this->uut->getRoutePath('unknown'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testBuildUrl()
        {
            $path = $this->uut->buildPath('default');
            $this->assertEquals('/default', $path);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testBuildUrlExtraArgs()
        {
            $path = $this->uut->buildPath('default', ['foo' => 'bar']);
            $this->assertEquals('/default', $path);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testBuildUrlWithArgs()
        {
            $path = $this->uut->buildPath('with-args', ['foo' => 'foo', 'bar' => 'bar']);
            $this->assertEquals('/with-args/foo/bar', $path);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testBuildUrlWithoutArgs()
        {
            $path = $this->uut->buildPath('with-args');
            $this->assertEquals('/with-args', $path);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testBuildUrlDefaultArgs()
        {
            $path = $this->uut->buildPath('omitted-args', ['foo' => 'foo']);
            $this->assertEquals('/omitted-args/foo/bar', $path);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testBuildUrlMissingArgs()
        {
            $path = $this->uut->buildPath('omitted-args');
            $this->assertEquals('/omitted-args/{foo}/bar', $path);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchDefault()
        {
            $response = $this->uut->dispatch($this->buildRequest('/default'));

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('default', $response->getContent());
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchWithArgs()
        {
            $response = $this->uut->dispatch($this->buildRequest('/with-args/foo/bar'));

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('foo-bar', $response->getContent());
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchOmittedArgs()
        {
            $response = $this->uut->dispatch($this->buildRequest('/omitted-args/foo'));

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('foo-bar', $response->getContent());
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchEvents()
        {
            $this->uut->setFireEvents(true)
                ->dispatch($this->buildRequest('/default'));

            $this->assertArrayHasKey(PreDispatch::class, self::$lastEvents);
            $this->assertArrayHasKey(PostRoute::class, self::$lastEvents);
            $this->assertArrayHasKey(PostDispatch::class, self::$lastEvents);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchErrorRouteController()
        {
            $this->expectException(IncompleteRouteException::class);

            $this->uut->dispatch($this->buildRequest('/missing-controller'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchErrorRouteAction()
        {
            $this->expectException(IncompleteRouteException::class);

            $this->uut->dispatch($this->buildRequest('/missing-action'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchErrorNoRoute()
        {
            $this->expectException(ResourceNotFoundException::class);

            $this->uut->dispatch($this->buildRequest('/unknown-path'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchErrorNoController()
        {
            $this->expectException(ResourceNotFoundException::class);

            $this->uut->dispatch($this->buildRequest('/unknown-controller'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchErrorNoAction()
        {
            $this->expectException(ResourceNotFoundException::class);

            $this->uut->dispatch($this->buildRequest('/unknown-action'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testDispatchErrorMissingArg()
        {
            $this->expectException(ResourceNotFoundException::class);

            $this->uut->dispatch($this->buildRequest('/missing-arg'));
        }

        /**
         * Tests the namesake method/feature
         */
        public function testForwardByObject()
        {
            $response = $this->uut->forward(new HelloWorldController(), 'default');

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals($response->getContent(), 'default');
        }

        /**
         * Tests the namesake method/feature
         */
        public function testForwardByClassName()
        {
            $response = $this->uut->forward('HelloWorldController', 'default');

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals($response->getContent(), 'default');
        }

        /**
         * Tests the namesake method/feature
         */
        public function testForwardEvents()
        {
            $this->uut->setFireEvents(true)
                ->forward(new HelloWorldController(), 'default');

            $this->assertArrayHasKey(PreForward::class, self::$lastEvents);
        }

        /**
         * Tests the namesake method/feature
         */
        public function testForwardErrorController()
        {
            $this->expectException(ClassNotFoundException::class);

            $this->uut->forward('UnknownController', 'foo');
        }

        /**
         * Tests the namesake method/feature
         */
        public function testForwardErrorAction()
        {
            $this->expectException(\InvalidArgumentException::class);

            $this->uut->forward('HelloWorldController', 'unknown');
        }

        /**
         * Tests the namesake method/feature
         */
        public function testForwardErrorRequiredArg()
        {
            $this->expectException(ResourceNotFoundException::class);

            $this->uut->forward('HelloWorldController', 'helloWorld');
        }
    }
}

namespace FF\Tests\Services\Dispatching\Controllers {

    use FF\Controllers\AbstractController;
    use FF\Services\Templating\TemplateRendererInterface;
    use FF\Services\Templating\TwigRenderer;
    use Symfony\Component\HttpFoundation\Response;

    class HelloWorldController extends AbstractController
    {
        public function default(): Response
        {
            return new Response('default');
        }

        public function helloWorld(string $foo, string $bar = 'baz'): Response
        {
            return new Response($foo . '-' . $bar);
        }

        /**
         * @return TemplateRendererInterface
         */
        protected function getTemplateRenderer(): TemplateRendererInterface
        {
            return new TwigRenderer(['template-dir' => 'foo']);
        }
    }
}
