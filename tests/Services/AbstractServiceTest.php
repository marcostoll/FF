<?php
/**
 * Definition of AbstractServiceTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Tests\Services;

use FF\Services\AbstractService;
use FF\Services\Exceptions\ConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * Test AbstractServiceTest
 *
 * @package FF\Tests
 */
class AbstractServiceTest extends TestCase
{
    const TEST_OPTIONS = ['foo' => 'bar'];

    /**
     * @var MyService
     */
    protected $uut;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->uut = new MyService(self::TEST_OPTIONS);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetOptions()
    {
        $this->assertEquals(self::TEST_OPTIONS, $this->uut->getOptions());
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetOption()
    {
        $this->assertEquals(self::TEST_OPTIONS['foo'], $this->uut->getOption('foo'));
        $this->assertNull($this->uut->getOption('unknown'));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetDefault()
    {
        $default = 'default';
        $this->assertEquals($default, $this->uut->getOption('unknown', $default));
    }

    /**
     * Tests the namesake method/feature
     */
    public function testConfigurationException()
    {
        $this->expectException(ConfigurationException::class);

        new MyService(['foo' => 'baz']);
    }

    /**
     * Tests the namesake method/feature
     */
    public function testGetClassIdentifier()
    {
        $this->assertEquals('MyService', MyService::getClassIdentifier());
    }
}

class MyService extends AbstractService
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