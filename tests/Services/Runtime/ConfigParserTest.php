<?php
/**
 * Definition of ConfigParserTest
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

use FF\Services\Runtime\ConfigParser;
use PHPUnit\Framework\TestCase;

/**
 * Test ConfigParserTest
 *
 * @package FF\Tests
 */
class ConfigParserTest extends TestCase
{
    /**
     * @var ConfigParser
     */
    protected $uut;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->uut = new ConfigParser();
    }
}
