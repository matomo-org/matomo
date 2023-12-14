<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Container\Container;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group Core
 * @group CoreAdminHome
 * @group Integration
 */
class ConfigDeleteTest extends ConsoleCommandTestCase
{
    /*
     * The text config:delete outputs when no matching value is found.
     */

    private const COMMAND = 'config:delete';
    private const CLASS_NAME_SHORT = 'ConfigDeleteTest';
    private const MSG_NOTHING_FOUND = 'Nothing found';

    /*
     * Path to store the test config file. It should be deleted when done.
     */
    private const TEST_CONFIG_PATH = '/tmp/test.config.ini.php';
    // Section 1.
    private const TEST_SECTION_1_NAME = self::CLASS_NAME_SHORT . '_test_section_1';
    // Setting 1.1
    private const TEST_SETTING_1_1_NAME = self::CLASS_NAME_SHORT . '_test_setting_1';
    private const TEST_SETTING_1_1_VALUE = self::CLASS_NAME_SHORT . '_test_value_1';
    // Setting 1.2
    private const TEST_SETTING_1_2_NAME = self::CLASS_NAME_SHORT . '_test_setting_2';
    private const TEST_SETTING_1_2_VALUE = self::CLASS_NAME_SHORT . '_test_value_2';
    // Setting 1.3 - IPv4 address with port
    private const TEST_SETTING_1_3_NAME = self::CLASS_NAME_SHORT . '_test_setting_3_ipv4_address';
    private const TEST_SETTING_1_3_VALUE = self::CLASS_NAME_SHORT . '123.123.123.123:8080';
    // Setting 1.4 - IPv6 address with port
    private const TEST_SETTING_1_4_NAME = self::CLASS_NAME_SHORT . '_test_setting_4_ipv6_address';
    private const TEST_SETTING_1_4_VALUE = self::CLASS_NAME_SHORT . '[2001:858:a6::186]:80';
    // Setting 1.5 - IPv4 subnet
    private const TEST_SETTING_1_5_NAME = self::CLASS_NAME_SHORT . '_test_setting_5_ipv6_subnet';
    private const TEST_SETTING_1_5_VALUE = self::CLASS_NAME_SHORT . '192.168.x.x/24';
    // Setting 1.6 - IPv6 subnet
    private const TEST_SETTING_1_6_NAME = self::CLASS_NAME_SHORT . '_test_setting_6_ipv6_subnet';
    private const TEST_SETTING_1_6_VALUE = self::CLASS_NAME_SHORT . '2001:db8::/32';
    // Setting 1.7 - mail address with extension
    private const TEST_SETTING_1_7_NAME = self::CLASS_NAME_SHORT . '_test_setting_7_mail_address';
    private const TEST_SETTING_1_7_VALUE = self::CLASS_NAME_SHORT . 'no-reply+with-extension@test.at';
    // Setting 1.8 - comma separated list
    private const TEST_SETTING_1_8_NAME = self::CLASS_NAME_SHORT . '_test_setting_8_comma_separated_list';
    private const TEST_SETTING_1_8_VALUE = self::CLASS_NAME_SHORT . '50,100,250,500,1000,2000,5000';

    // Section 2.
    private const TEST_SECTION_2_NAME = self::CLASS_NAME_SHORT . '_test_section_2';
    // Setting 2.1
    private const TEST_SETTING_2_1_NAME = self::CLASS_NAME_SHORT . '_array_setting_1';
    private const TEST_SETTING_2_1_VALUE_0 = self::CLASS_NAME_SHORT . '_arr_val_1';
    private const TEST_SETTING_2_1_VALUE_1 = self::CLASS_NAME_SHORT . '_arr_val_2';
    private const TEST_SETTING_2_1_VALUE_2 = self::CLASS_NAME_SHORT . '_arr_val_3';
    private const TEST_SETTING_2_1_VALUES = [self::TEST_SETTING_2_1_VALUE_0, self::TEST_SETTING_2_1_VALUE_1, self::TEST_SETTING_2_1_VALUE_2];
    // Setting 2.2 - IPv4 address with port
    private const TEST_SETTING_2_2_NAME = self::CLASS_NAME_SHORT . '_array_setting_2_ipv4_address';
    private const TEST_SETTING_2_2_VALUE_0 = self::CLASS_NAME_SHORT . '123.123.123.123:8080';
    private const TEST_SETTING_2_2_VALUE_1 = self::CLASS_NAME_SHORT . '234.234.234.234:8080';
    private const TEST_SETTING_2_2_VALUE_2 = self::CLASS_NAME_SHORT . '16.16.16.16:5423';
    private const TEST_SETTING_2_2_VALUES = [self::TEST_SETTING_2_2_VALUE_0, self::TEST_SETTING_2_2_VALUE_1, self::TEST_SETTING_2_2_VALUE_2];
    // Setting 2.3 - IPv6 address with port
    private const TEST_SETTING_2_3_NAME = self::CLASS_NAME_SHORT . '_array_setting_3_ipv6_address';
    private const TEST_SETTING_2_3_VALUE_0 = self::CLASS_NAME_SHORT . '[2001:858:dead::186]:80';
    private const TEST_SETTING_2_3_VALUE_1 = self::CLASS_NAME_SHORT . '[2001:858:beef::186]:180';
    private const TEST_SETTING_2_3_VALUE_2 = self::CLASS_NAME_SHORT . '[2001:858:cafe::186]:9080';
    private const TEST_SETTING_2_3_VALUES = [self::TEST_SETTING_2_3_VALUE_0, self::TEST_SETTING_2_3_VALUE_1, self::TEST_SETTING_2_3_VALUE_2];
    // Setting 2.4 - IPv4 subnet
    private const TEST_SETTING_2_4_NAME = self::CLASS_NAME_SHORT . '_array_setting_4_ipv4_subnet';
    private const TEST_SETTING_2_4_VALUE_0 = self::CLASS_NAME_SHORT . '192.168.x.x/24';
    private const TEST_SETTING_2_4_VALUE_1 = self::CLASS_NAME_SHORT . '172.16.x.x/30';
    private const TEST_SETTING_2_4_VALUE_2 = self::CLASS_NAME_SHORT . '10.0.0.0/16';
    private const TEST_SETTING_2_4_VALUES = [self::TEST_SETTING_2_4_VALUE_0, self::TEST_SETTING_2_4_VALUE_1, self::TEST_SETTING_2_4_VALUE_2];
    // Setting 2.5 - IPv6 subnet
    private const TEST_SETTING_2_5_NAME = self::CLASS_NAME_SHORT . '_array_setting_5_ipv6_subnet';
    private const TEST_SETTING_2_5_VALUE_0 = self::CLASS_NAME_SHORT . '2001:db8:dead:/32';
    private const TEST_SETTING_2_5_VALUE_1 = self::CLASS_NAME_SHORT . '2001:db8:beef:/32';
    private const TEST_SETTING_2_5_VALUE_2 = self::CLASS_NAME_SHORT . '2001:858:cafe:/64';
    private const TEST_SETTING_2_5_VALUES = [self::TEST_SETTING_2_5_VALUE_0, self::TEST_SETTING_2_5_VALUE_1, self::TEST_SETTING_2_5_VALUE_2];
    // Setting 2.6 - mail address with extension
    private const TEST_SETTING_2_6_NAME = self::CLASS_NAME_SHORT . '_array_setting_6_mail_address';
    private const TEST_SETTING_2_6_VALUE_0 = self::CLASS_NAME_SHORT . 'no-reply+with_extension@test.at';
    private const TEST_SETTING_2_6_VALUE_1 = self::CLASS_NAME_SHORT . 'your-mail_address@example.com';
    private const TEST_SETTING_2_6_VALUE_2 = self::CLASS_NAME_SHORT . 'noreply@example.com';
    private const TEST_SETTING_2_6_VALUES = [self::TEST_SETTING_2_6_VALUE_0, self::TEST_SETTING_2_6_VALUE_1, self::TEST_SETTING_2_6_VALUE_2];
    // Setting 2.7 - comma separated list
    private const TEST_SETTING_2_7_NAME = self::CLASS_NAME_SHORT . '_array_setting_7_commaseparated_list';
    private const TEST_SETTING_2_7_VALUE_0 = self::CLASS_NAME_SHORT . '50,100,250,500,1000,2000,5000';
    private const TEST_SETTING_2_7_VALUE_1 = self::CLASS_NAME_SHORT . '1,1,2,3,5,8,13,21,34,55,89,144,233,377,610,987,1597';
    private const TEST_SETTING_2_7_VALUE_2 = self::CLASS_NAME_SHORT . 'm,a,t,o,m,o,r,p,h,o,s,i,s';
    private const TEST_SETTING_2_7_VALUES = [self::TEST_SETTING_2_7_VALUE_0, self::TEST_SETTING_2_7_VALUE_1, self::TEST_SETTING_2_7_VALUE_2];

    public static function setUpBeforeClass(): void
    {
        self::removeTestConfigFile();
        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        self::removeTestConfigFile();
        parent::setUp();
    }

    private static function getTestConfigFilePath()
    {
        return PIWIK_INCLUDE_PATH . self::TEST_CONFIG_PATH;
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            // use a config instance that will save to a test INI file
            'Piwik\Config' => function (Container $container) {
                /** @var GlobalSettingsProvider $actualGlobalSettingsProvider */
                $actualGlobalSettingsProvider = $container->get('Piwik\Application\Kernel\GlobalSettingsProvider');

                $config = self::makeTestConfig();

                // copy over sections required for tests
                $config->tests = $actualGlobalSettingsProvider->getSection('tests');
                $config->database = $actualGlobalSettingsProvider->getSection('database_tests');

                return $config;
            },
        );
    }

    private static function makeTestConfig()
    {
        $settingsProvider = new GlobalSettingsProvider(null, self::getTestConfigFilePath());
        $config = new Config($settingsProvider);

        // Add the first section.
        $sectionName = self::TEST_SECTION_1_NAME;
        $config->$sectionName[self::TEST_SETTING_1_1_NAME] = self::TEST_SETTING_1_1_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_2_NAME] = self::TEST_SETTING_1_2_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_3_NAME] = self::TEST_SETTING_1_3_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_4_NAME] = self::TEST_SETTING_1_4_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_5_NAME] = self::TEST_SETTING_1_5_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_6_NAME] = self::TEST_SETTING_1_6_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_7_NAME] = self::TEST_SETTING_1_7_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_8_NAME] = self::TEST_SETTING_1_8_VALUE;

        // Add a second section so we are testing that we do not accidentally return it.
        $sectionName = self::TEST_SECTION_1_NAME . '_second_section';
        // Add a setting with the same name as in section #1 but with a random int value.
        $config->$sectionName[self::TEST_SETTING_1_1_NAME] = random_int(PHP_INT_MIN, PHP_INT_MAX);
        // Add another setting to the same section with some bogus content.
        $config->$sectionName[self::TEST_SETTING_1_2_NAME . '_another'] = '127.0.0.1';


        // Add the second section.
        // Add an array value like section=PluginsInstalled; setting=PluginsInstalled[].
        $sectionName = self::TEST_SECTION_2_NAME;
        // Add some values to the setting array.
        $config->$sectionName[self::TEST_SETTING_2_1_NAME] = self::TEST_SETTING_2_1_VALUES;
        $config->$sectionName[self::TEST_SETTING_2_2_NAME] = self::TEST_SETTING_2_2_VALUES;
        $config->$sectionName[self::TEST_SETTING_2_3_NAME] = self::TEST_SETTING_2_3_VALUES;
        $config->$sectionName[self::TEST_SETTING_2_4_NAME] = self::TEST_SETTING_2_4_VALUES;
        $config->$sectionName[self::TEST_SETTING_2_5_NAME] = self::TEST_SETTING_2_5_VALUES;
        $config->$sectionName[self::TEST_SETTING_2_6_NAME] = self::TEST_SETTING_2_6_VALUES;
        $config->$sectionName[self::TEST_SETTING_2_7_NAME] = self::TEST_SETTING_2_7_VALUES;

        $config->forceSave();
        return $config;
    }

    private static function removeTestConfigFile()
    {
        $configPath = self::getTestConfigFilePath();
        if (file_exists($configPath)) {
            unlink($configPath);
        }
    }

    private static function makeNewConfig()
    {
        $settings = new GlobalSettingsProvider(null, self::getTestConfigFilePath());
        return new Config($settings);
    }

    private function runCommandWithOptions(string $sectionName, string $settingName, string $value = ''): object
    {

        $inputArr = [
            'command' => self::COMMAND,
            '--section' => $sectionName,
            '--key' => $settingName,
            '-vvv' => false,
        ];
        if (!empty($value)) {
            $inputArr['--value'] = $value;
        }
        $exitCode = $this->applicationTester->run($inputArr);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));

        // Put the results in an easy-to-handle object format.
        return (object) ['exitCode' => $exitCode, 'output' => $output];
    }

    private function runCommandWithArguments(string $sectionName, string $settingName = '', string $value = ''): object
    {

        $inputArr = [
            'command' => self::COMMAND,
            '-vvv' => false,
            'argument' => $sectionName . '.' . $settingName . (empty($value) ? '' : ".$value"),
        ];

        $exitCode = $this->applicationTester->run($inputArr);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));

        // Put the results in an easy-to-handle object format.
        return (object) ['exitCode' => $exitCode, 'output' => $output];
    }

    //
    //*************************************************************************
    // Tests that should yield errors.
    //*************************************************************************
    //
    public function testNoArgsShouldYieldError()
    {

        $inputArr = [
            'command' => 'config:get',
            '-vvv' => false,
        ];
        $exitCode = $this->applicationTester->run($inputArr);

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $exitCode);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));

        $this->assertStringContainsString('InvalidArgumentException', $output);
    }

    public function testEmptyArgsShouldYieldError()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithArguments('');

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    public function testEmptyOptionsShouldYieldError()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions('', '');

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    public function testSetArgsAndOptionsShouldYieldError()
    {
        $inputArr = [
            'command' => 'config:get',
            'argument' => self::TEST_SECTION_1_NAME . '.' . self::TEST_SETTING_1_1_NAME,
            '--section' => self::TEST_SECTION_1_NAME,
            '--key' => self::TEST_SETTING_1_1_NAME,
            '-vvv' => false,
        ];
        $exitCode = $this->applicationTester->run($inputArr);

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $exitCode);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));

        $this->assertStringContainsString('InvalidArgumentException', $output);
    }

    public function testEmptySectionShouldYieldError()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions('', self::TEST_SETTING_1_1_NAME);

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    public function testScalarSettingWithArrayValShouldYieldError()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_1_NAME, self::CLASS_NAME_SHORT . '_Array_key_does_not_exist');

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    public function testArrayWithNoValShouldYieldError()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME);

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    //
    //*************************************************************************
    // Tests for nonexistent data.
    //*************************************************************************
    //
    public function testUsingOptsNonExistentSectionShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::CLASS_NAME_SHORT . '_Section_does_not_exist', self::TEST_SETTING_1_1_NAME);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingArgsNonExistentSectionShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithArguments(self::CLASS_NAME_SHORT . '_Section_does_not_exist', self::TEST_SETTING_1_1_NAME);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingOptsNonExistentSectionAndSettingShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::CLASS_NAME_SHORT . '_Section_does_not_exist', self::CLASS_NAME_SHORT . '_Setting_does_not_exist');

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingArgsNonExistentSectionAndSettingShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithArguments(self::CLASS_NAME_SHORT . '_Section_does_not_exist', self::CLASS_NAME_SHORT . '_Setting_does_not_exist');

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingOptsNonExistentSettingShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, self::CLASS_NAME_SHORT . '_Setting_does_not_exist');

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingArgsNonExistentSettingShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithArguments(self::TEST_SECTION_1_NAME, self::CLASS_NAME_SHORT . '_Setting_does_not_exist');

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingOptsArrayWithInvalidValShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME, self::CLASS_NAME_SHORT . '_Array_key_does_not_exist');

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testUsingArgsArrayWithInvalidValShouldYieldEmpty()
    {

        // Pass empty section name.
        $resultObj = $this->runCommandWithArguments(self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME, self::CLASS_NAME_SHORT . '_Array_key_does_not_exist');

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    //
    //*************************************************************************
    // Tests for existing data.
    //*************************************************************************
    //

    /**
     * @dataProvider getSingleSettingDataProvider1
     */
    public function testUsingOptsDeleteSingleSetting($sectionName, $settingName, $settingValue)
    {

        $resultObj = $this->runCommandWithOptions($sectionName, $settingName);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $this->assertStringContainsString('Success:', $resultObj->output);

        $config = $this->makeNewConfig();
        $configDump = $config->dumpConfig();
        $needle = $settingName . ' = ' . $settingValue;
        $this->assertStringNotContainsString($needle, $configDump);
    }

    /**
     * @dataProvider getSingleSettingDataProvider2
     */
    public function testUsingArgsDeleteSingleSetting($sectionName, $settingName, $settingValue)
    {

        $resultObj = $this->runCommandWithArguments($sectionName, $settingName);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $this->assertStringContainsString('Success:', $resultObj->output);

        $config = $this->makeNewConfig();
        $configDump = $config->dumpConfig();
        $needle = $settingName . ' = ' . $settingValue;
        $this->assertStringNotContainsString($needle, $configDump);
    }

    /**
     * @dataProvider getArraySettingDataProvider1
     */
    public function testUsingOptsDeleteArraySetting($sectionName, $settingName, $settingValue)
    {

        $resultObj = $this->runCommandWithOptions($sectionName, $settingName, $settingValue);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $this->assertStringContainsString('Success:', $resultObj->output);

        $config = $this->makeNewConfig();
        $configDump = $config->dumpConfig();
        $needle = $settingName . ' = ' . $settingValue;
        $this->assertStringNotContainsString($needle, $configDump);
    }

    /**
     * @dataProvider getArraySettingDataProvider2
     */
    public function testUsingArgsDeleteArraySetting($sectionName, $settingName, $settingValue)
    {

        $resultObj = $this->runCommandWithArguments($sectionName, $settingName, $settingValue);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $this->assertStringContainsString('Success:', $resultObj->output);

        $config = $this->makeNewConfig();
        $configDump = $config->dumpConfig();
        $needle = $settingName . ' = ' . $settingValue;
        $this->assertStringNotContainsString($needle, $configDump);
    }

    public function getSingleSettingDataProvider1()
    {
        yield 'Section 1, Setting 1.1'                                  => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_1_NAME, self::TEST_SETTING_1_1_VALUE];
        yield 'Section 1, Setting 1.2'                                  => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_2_NAME, self::TEST_SETTING_1_2_VALUE];
        yield 'Section 1, Setting 1.3 - IPv4 address with port'         => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_3_NAME, self::TEST_SETTING_1_3_VALUE];
        yield 'Section 1, Setting 1.4 - IPv6 address with port'         => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_4_NAME, self::TEST_SETTING_1_4_VALUE];
    }

    public function getSingleSettingDataProvider2()
    {
        yield 'Section 1, Setting 1.5 - IPv4 subnet'                    => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_5_NAME, self::TEST_SETTING_1_5_VALUE];
        yield 'Section 1, Setting 1.6 - IPv6 subnet'                    => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_6_NAME, self::TEST_SETTING_1_6_VALUE];
        yield 'Section 1, Setting 1.7 - mail address with extension'    => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_7_NAME, self::TEST_SETTING_1_7_VALUE];
        yield 'Section 1, Setting 1.8 - comma separated list'           => [self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_8_NAME, self::TEST_SETTING_1_8_VALUE];
    }

    public function getArraySettingDataProvider1()
    {
        yield 'Section 2, Setting 2.1'                                  => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME, self::TEST_SETTING_2_1_VALUE_0];
        yield 'Section 2, Setting 2.2 - IPv4 address with port'         => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_2_NAME, self::TEST_SETTING_2_2_VALUE_0];
        yield 'Section 2, Setting 2.3 - IPv6 address with port'         => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_3_NAME, self::TEST_SETTING_2_3_VALUE_0];
        yield 'Section 2, Setting 2.4 - IPv4 subnet'                    => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_4_NAME, self::TEST_SETTING_2_4_VALUE_0];
        yield 'Section 2, Setting 2.5 - IPv6 subnet'                    => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_5_NAME, self::TEST_SETTING_2_5_VALUE_0];
        yield 'Section 2, Setting 2.6 - mail address with extension'    => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_6_NAME, self::TEST_SETTING_2_6_VALUE_0];
        yield 'Section 2, Setting 2.7 - comma separated list'           => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_7_NAME, self::TEST_SETTING_2_7_VALUE_0];
    }

    public function getArraySettingDataProvider2()
    {
        yield 'Section 2, Setting 2.1'                                  => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME, self::TEST_SETTING_2_1_VALUE_1];
        yield 'Section 2, Setting 2.2 - IPv4 address with port'         => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_2_NAME, self::TEST_SETTING_2_2_VALUE_1];
        yield 'Section 2, Setting 2.3 - IPv6 address with port'         => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_3_NAME, self::TEST_SETTING_2_3_VALUE_1];
        yield 'Section 2, Setting 2.4 - IPv4 subnet'                    => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_4_NAME, self::TEST_SETTING_2_4_VALUE_1];
        yield 'Section 2, Setting 2.5 - IPv6 subnet'                    => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_5_NAME, self::TEST_SETTING_2_5_VALUE_1];
        yield 'Section 2, Setting 2.6 - mail address with extension'    => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_6_NAME, self::TEST_SETTING_2_6_VALUE_1];
        yield 'Section 2, Setting 2.7 - comma separated list'           => [self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_7_NAME, self::TEST_SETTING_2_7_VALUE_1];
    }
}
