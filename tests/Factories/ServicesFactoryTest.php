<?php
/**
 * Definition of ServicesFactoryTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Factories {

    use FF\Factories\ClassLocators\BaseNamespaceClassLocator;
    use FF\Factories\Exceptions\ClassNotFoundException;
    use FF\Factories\ServicesFactory;
    use FF\Services\Exceptions\ConfigurationException;
    use FF\Tests\Services\ServiceOne;
    use FF\Tests\Services\ServiceTwo;
    use PHPUnit\Framework\TestCase;

    /**
     * Test ServicesFactoryTest
     *
     * @package FF\Tests
     */
    class ServicesFactoryTest extends TestCase
    {
        const TEST_OPTIONS = ['ServiceOne' => ['foo' => 'bar']];

        /**
         * {@inheritdoc}
         */
        public static function setUpBeforeClass(): void
        {
            ServicesFactory::clearInstance();
        }

        /**
         * Tests the namesake method/feature
         */
        public function testGetInstanceConfigException()
        {
            $this->expectException(ConfigurationException::class);

            ServicesFactory::getInstance();
        }

        /**
         * Tests the namesake method/feature
         */
        public function testClearInstance()
        {
            $this->expectException(ConfigurationException::class);

            $instance = new ServicesFactory(self::TEST_OPTIONS);
            $instance->getClassLocator()->prependNamespaces('FF\Tests');
            ServicesFactory::setInstance($instance);
            ServicesFactory::clearInstance();
            ServicesFactory::getInstance();
        }

        /**
         * Tests the namesake method/feature
         */
        public function testSetGetInstance()
        {
            $instance = new ServicesFactory(self::TEST_OPTIONS);
            $instance->getClassLocator()->prependNamespaces('FF\Tests');
            ServicesFactory::setInstance($instance);

            $this->assertSame($instance, ServicesFactory::getInstance());
        }

        /**
         * Tests the namesake method/feature
         */
        public function testGetClassLocator()
        {
            $this->assertInstanceOf(
                BaseNamespaceClassLocator::class,
                ServicesFactory::getInstance()->getClassLocator()
            );
        }

        /**
         * Tests the namesake method/feature
         *
         * @depends testSetGetInstance
         */
        public function testSetServiceOptions()
        {
            $this->assertEquals(
                self::TEST_OPTIONS['ServiceOne'],
                ServicesFactory::getInstance()->getServiceOptions('ServiceOne')
            );
            $this->assertEquals([], ServicesFactory::getInstance()->getServiceOptions('ServiceTwo'));
            $this->assertEquals([], ServicesFactory::getInstance()->getServiceOptions('unknown'));
        }

        /**
         * Tests the namesake method/feature
         *
         * @depends testSetGetInstance
         */
        public function testGetSingle()
        {
            $service = ServicesFactory::getInstance()->get('ServiceOne');
            $this->assertInstanceOf(ServiceOne::class, $service);
            $this->assertEquals(self::TEST_OPTIONS['ServiceOne'], $service->getOptions());
        }

        /**
         * Tests the namesake method/feature
         *
         * @depends testSetGetInstance
         */
        public function testGetMultiples()
        {
            $services = ServicesFactory::getInstance()->get('ServiceOne', 'ServiceTwo');
            $this->assertEquals(2, count($services));
            $this->assertInstanceOf(ServiceOne::class, $services[0]);
            $this->assertInstanceOf(ServiceTwo::class, $services[1]);
        }

        /**
         * Tests the namesake method/feature
         *
         * @depends testSetGetInstance
         */
        public function testGetClassNotFound()
        {
            $this->expectException(ClassNotFoundException::class);

            ServicesFactory::getInstance()->get('ServiceUnknown');
        }
    }
}

namespace FF\Tests\Services {

    use FF\Services\AbstractService;

    class ServiceOne extends AbstractService
    {
        protected function validateOptions(array $options, array &$errors): bool
        {
            if (isset($options['foo']) && $options['foo'] != 'bar') {
                $errors[] = 'foo is not bar';
                return false;
            }

            return parent::validateOptions($options, $errors);
        }
    }

    class ServiceTwo extends AbstractService
    {

    }
}