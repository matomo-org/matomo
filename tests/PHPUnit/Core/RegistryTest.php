<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Registry;

class RegistryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testGetInstance()
    {
        $instance = Registry::getInstance();

        $this->assertInstanceOf('\Piwik\Registry', $instance);

        // check singleton
        $this->assertSame(Registry::getInstance(), $instance);
    }

    /**
     * @group Core
     */
    public function testFuncionality()
    {
        $value = 'newValue';
        $key   = 'newKey';

        Registry::set($key, $value);
        $this->assertEquals($value, Registry::get($key));
        $this->assertTrue(Registry::isRegistered($key));
    }
}
