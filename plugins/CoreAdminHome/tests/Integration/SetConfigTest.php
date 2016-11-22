<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Interop\Container\ContainerInterface;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Url;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 */
class SetConfigTest extends ConsoleCommandTestCase
{
    const TEST_CONFIG_PATH = '/tmp/test.config.ini.php';

    public static function setUpBeforeClass()
    {
        self::removeTestConfigFile();

        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        self::removeTestConfigFile();

        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->makeLocalConfigWritable();
    }

    public function test_Command_SucceedsWhenOptionsUsed()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            '--section' => 'MySection',
            '--key' => 'setting',
            '--value' => 'myvalue',
            '-vvv' => false,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        $config = $this->makeNewConfig();
        $this->assertEquals(array('setting' => 'myvalue'), $config->MySection);

        $this->assertContains('Setting [MySection] setting = "myvalue"', $this->applicationTester->getDisplay());
    }

    /**
     * @dataProvider getInvalidArgumentsForTest
     */
    public function test_Command_FailsWhenInvalidArgumentsUsed($invalidArgument)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            'assignment' => array($invalidArgument),
            '-vvv' => false,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains('Invalid assignment string', $this->applicationTester->getDisplay());
    }

    public function getInvalidArgumentsForTest()
    {
        return array(
            array("garbage"),
            array("ab&cd.ghi=23"),
            array("section.value = 34"),
            array("section.value = notjson"),
            array("section.array[0]=23"),
        );
    }

    public function test_Command_FailsWithMissingFilePermissionException_whenConfigFileNotWritable()
    {
        $this->makeLocalConfigNotWritable();

        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            'assignment' => array(
                'MySection.other_array_value=[]',
            ),
            '-vvv' => false,
        ));

        $this->assertNotEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains('[Piwik\Exception\MissingFilePermissionException]', $this->applicationTester->getDisplay());
    }

    public function test_Command_SucceedsWhenArgumentsUsed()
    {
        $config = Config::getInstance();
        $config->General['trusted_hosts'] = array('www.trustedhost.com');
        $config->MySection['other_array_value'] = array('1', '2');
        $config->forceSave();

        $code = $this->applicationTester->run(array(
            'command' => 'config:set',
            'assignment' => array(
                'General.action_url_category_delimiter="+"',
                'General.trusted_hosts[]="www.trustedhost2.com"',
                'MySection.array_value=["abc","def"]',
                'MySection.object_value={"abc":"def"}',
                'MySection.other_array_value=[]',
            ),
            '-vvv' => false,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        $config = self::makeNewConfig(); // create a new config instance so we read what's in the file

        $this->assertEquals('+', $config->General['action_url_category_delimiter']);
        $this->assertEquals(array('www.trustedhost.com', 'www.trustedhost2.com'), $config->General['trusted_hosts']);
        $this->assertEquals(array('abc', 'def'), $config->MySection['array_value']);
        $this->assertEquals(array('def'), $config->MySection['object_value']);
        $this->assertArrayNotHasKey('other_array_value', $config->MySection);

        $this->assertContains("Done.", $this->applicationTester->getDisplay());
    }

    /**
     * @dataProvider getOptionsForSettingValueToZeroTests
     */
    public function test_Command_SucceedsWhenSettingValueToZero($options)
    {
        $config = Config::getInstance();
        $config->Tracker['debug'] = 1;
        $config->forceSave();

        $code = $this->applicationTester->run($options);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        $config = self::makeNewConfig();

        $this->assertEquals(0, $config->Tracker['debug']);
        $this->assertContains("Done.", $this->applicationTester->getDisplay());
    }

    public function getOptionsForSettingValueToZeroTests()
    {
        return array(
            array(
                array(
                    'command' => 'config:set',
                    '--section' => 'Tracker',
                    '--key' => 'debug',
                    '--value' => 0,
                ),
            ),
            array(
                array(
                    'command' => 'config:set',
                    'assignment' => array(
                        'Tracker.debug=0',
                    ),
                ),
            ),
        );
    }

    private static function getTestConfigFilePath()
    {
        return PIWIK_INCLUDE_PATH . self::TEST_CONFIG_PATH;
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            // use a config instance that will save to a test INI file
            'Piwik\Config' => function (ContainerInterface $c) {
                /** @var GlobalSettingsProvider $actualGlobalSettingsProvider */
                $actualGlobalSettingsProvider = $c->get('Piwik\Application\Kernel\GlobalSettingsProvider');

                $config = SetConfigTest::makeNewConfig();

                // copy over sections required for tests
                $config->tests = $actualGlobalSettingsProvider->getSection('tests');
                $config->database = $actualGlobalSettingsProvider->getSection('database');
                $config->database_tests = $actualGlobalSettingsProvider->getSection('database_tests');

                return $config;
            },
        );
    }

    private static function makeNewConfig()
    {
        $settings = new GlobalSettingsProvider(null, SetConfigTest::getTestConfigFilePath());
        return new Config($settings);
    }

    private static function removeTestConfigFile()
    {
        $configPath = self::getTestConfigFilePath();
        if (file_exists($configPath)) {
            unlink($configPath);
        }
    }

    protected function makeLocalConfigNotWritable()
    {
        $local = Config::getInstance()->getLocalPath();
        touch($local);
        chmod($local, 0444);
        $this->assertFalse(is_writable($local));
    }

    protected function makeLocalConfigWritable()
    {
        $local = Config::getInstance()->getLocalPath();
        @chmod(dirname($local), 0755);
        @chmod($local, 0755);
        $this->assertTrue(is_writable(dirname($local)));
        if(file_exists($local)) {
            $this->assertTrue(is_writable($local));
        }
    }
}
