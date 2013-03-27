<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class Plugin_ConfigTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $path = PIWIK_USER_PATH . '/plugins/ExamplePlugin/config/local.config.php';
        if (file_exists($path)) {
            @unlink($path);
        }

        $this->assertFalse(file_exists($path), 'unable to remove local.config.php');
    }

    /**
     * @group Core
     * @group Plugin
     * @group Plugin_Config
     */
    function testLoadWithNoConfig()
    {
        $objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');

        $this->assertFalse($objectUnderTest->load(), 'load() with no config should fail');
    }

    /**
     * @group Core
     * @group Plugin
     * @group Plugin_Config
     */
    function testLoadAlternatePath()
    {
        $objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin', 'local.config.sample.php');
        $config = $objectUnderTest->load();

        $this->assertTrue($config !== false);
        $this->assertTrue($config['id'] === 'Example');
        $this->assertTrue($config['name'] === 'ExamplePlugin');
        $this->assertTrue($config['description'] === 'This is an example');
    }

    /**
     * @group Core
     * @group Plugin
     * @group Plugin_Config
     */
    function testLoad()
    {
        $dir = PIWIK_USER_PATH . '/plugins/ExamplePlugin/config';
        @copy($dir . '/local.config.sample.php', $dir . '/local.config.php');

        $objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');
        $config = $objectUnderTest->load();

        $this->assertTrue($config !== false);
        $this->assertTrue($config['id'] === 'Example');
        $this->assertTrue($config['name'] === 'ExamplePlugin');
        $this->assertTrue($config['description'] === 'This is an example');
    }

    /**
     * @group Core
     * @group Plugin
     * @group Plugin_Config
     */
    function testStore()
    {
        $config = array(
            1, 'mixed', array('a'), 'b' => 'c'
        );

        $objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');
        $objectUnderTest->store($config);

        $path = PIWIK_USER_PATH . '/plugins/ExamplePlugin/config/local.config.php';
        $this->assertTrue(file_exists($path));

        $objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');
        $newConfig = $objectUnderTest->load();

        $this->assertTrue($config !== false);
        $this->assertTrue($config[0] === 1);
        $this->assertTrue($config[1] === 'mixed');
        $this->assertTrue(is_array($config[2]) && $config[2][0] === 'a');
        $this->assertTrue($config['b'] === 'c');
    }
}
