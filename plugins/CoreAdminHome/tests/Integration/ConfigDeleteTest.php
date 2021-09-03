<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Psr\Container\ContainerInterface;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Integration
 * @usage
 *   All tests: ./console tests:run /opt/bitnami/matomo/plugins/CoreAdminHome/tests/Integration/ConfigGetTest.php
 *   One test: cd tests/PHPUnit && vendor/bin/phpunit --filter test_Command_GetSectionWithArray plugins/CoreAdminHome/tests/Integration/ConfigGetTest.php; cd ../..
 */
class ConfigGetTest extends ConsoleCommandTestCase
{
    /*
     * The text config:get outputs when no matching value is found.
     */

    private const NOTHING_FOUND = 'Nothing found.';

    /*
     * Path to store the test config file. It should be deleted when done.
     */
    private const TEST_CONFIG_PATH = '/tmp/test.config.ini.php';
    // A section with a single value.
    private const TEST_SECTION1_NAME = 'ConfigGetTest_test_section';
    private const TEST_SETTING_1_1_NAME = 'ConfigGetTest_test_setting_1';
    private const TEST_SETTING_1_1_VALUE = 'ConfigGetTest_test_value_1';
    private const TEST_SETTING_1_2_NAME = 'ConfigGetTest_test_setting_2';
    private const TEST_SETTING_1_2_VALUE = 'ConfigGetTest_test_value_2';
    private const TEST_SETTING_1_ARR = [
        self::TEST_SETTING_1_1_NAME => self::TEST_SETTING_1_1_VALUE,
        self::TEST_SETTING_1_2_NAME => self::TEST_SETTING_1_2_VALUE,
    ];
    // A section with an array value.
    private const TEST_SECTION_NAME_WITH_ARRAY = 'ConfigGetTest_test_section_with_array';
    private const TEST_SETTING_NAME_OF_ARRAY = 'ConfigGetTest_array_setting';
    private const TEST_SETTING_VALUES_OF_ARRAY = ['ConfigGetTest_arr_val1', 'ConfigGetTest_arr_val2', 'ConfigGetTest_arr_val3'];

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

    public function tearDown(): void
    {
        parent::tearDown();
    }

    private static function getTestConfigFilePath()
    {
        return PIWIK_INCLUDE_PATH . self::TEST_CONFIG_PATH;
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            // use a config instance that will save to a test INI file
            'Piwik\Config' => function (ContainerInterface $containerInterface) {
                /** @var GlobalSettingsProvider $actualGlobalSettingsProvider */
                $actualGlobalSettingsProvider = $containerInterface->get('Piwik\Application\Kernel\GlobalSettingsProvider');

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
        $settingsProvider = new GlobalSettingsProvider(null, ConfigGetTest::getTestConfigFilePath());
        $config = new Config($settingsProvider);

        // Add the first section.
        $sectionName = self::TEST_SECTION1_NAME;
        $config->$sectionName[self::TEST_SETTING_1_1_NAME] = self::TEST_SETTING_1_1_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_2_NAME] = self::TEST_SETTING_1_2_VALUE;

        // Add a second section so we are testing that we do not accidentally return it.
        $sectionName = self::TEST_SECTION1_NAME . '_second_section';
        // Add a setting with the same name as in section #1 but with a random int value.
        $config->$sectionName[self::TEST_SETTING_1_1_NAME] = random_int(PHP_INT_MIN, PHP_INT_MAX);
        // Add another setting to the same section with some bogus content.
        $config->$sectionName[self::TEST_SETTING_1_2_NAME . '_another'] = '127.0.0.1';

        // Add an array value like section=PluginsInstalled; setting=PluginsInstalled[].
        $sectionName = self::TEST_SECTION_NAME_WITH_ARRAY;
        // Add some values to the setting array.
        $config->$sectionName[self::TEST_SETTING_NAME_OF_ARRAY] = self::TEST_SETTING_VALUES_OF_ARRAY;

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

    private function runCommandWithOptions(string $sectionName, string $settingName = '', $format = 'json'): object
    {
        $debug = true;
        $debug && fwrite(STDERR, PHP_EOL . str_repeat('-', 80) . PHP_EOL . __FUNCTION__ . "::Started with \$sectionName=$sectionName; \$settingName=$settingName; \$format=$format") . PHP_EOL;

        $inputArr = [
            'command' => 'config:get',
            '--section' => $sectionName,
            '--key' => $settingName,
            '-vvv' => false,
        ];
        // To allow using default format, only add the format option if specified.
        if ( ! empty($format)) {
            $inputArr['--format'] = $format;
        }
        $exitCode = $this->applicationTester->run($inputArr);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        // Put the results in an easy-to-handle object format.
        return (object) ['exitCode' => $exitCode, 'output' => $output];
    }

    private function runCommandWithArguments(string $sectionName, string $settingName = '', $format = 'json'): object
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . str_repeat('-', 80) . PHP_EOL . __FUNCTION__ . "::Started with \$sectionName=$sectionName; \$settingName=$settingName; \$format=$format") . PHP_EOL;

        $inputArr = [
            'command' => 'config:get',
            '-vvv' => false,
            'argument' => $sectionName . (empty($settingName) ? '' : ".$settingName"),
        ];
        // To allow using default format, only add the format option if specified.
        if ( ! empty($format)) {
            $inputArr['--format'] = $format;
        }
        $exitCode = $this->applicationTester->run($inputArr);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        // Put the results in an easy-to-handle object format.
        return (object) ['exitCode' => $exitCode, 'output' => $output];
    }

    //
    //*************************************************************************
    // Tests that should yield errors.
    //*************************************************************************
    //

    public function test_Command_NoArgsShouldYieldError()
    {
        $debug = false;

        $inputArr = [
            'command' => 'config:get',
            '-vvv' => false,
        ];
        $exitCode = $this->applicationTester->run($inputArr);

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $exitCode);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        $this->assertStringContainsString('InvalidArgumentException', $output);
    }

    public function test_Command_EmptyArgsShouldYieldError()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithArguments('');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    public function test_Command_EmptyOptionsShouldYieldError()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions('');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    public function test_Command_SetArgsAndOptionsShouldYieldError()
    {
        $debug = false;
        $inputArr = [
            'command' => 'config:get',
            'argument' => self::TEST_SECTION1_NAME . '.' . self::TEST_SETTING_1_1_NAME,
            '--section' => self::TEST_SECTION1_NAME,
            '--key' => self::TEST_SETTING_1_1_NAME,
            '-vvv' => false,
        ];
        $exitCode = $this->applicationTester->run($inputArr);

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $exitCode);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        $this->assertStringContainsString('InvalidArgumentException', $output);
    }

    public function test_Command_EmptySectionShouldYieldError()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions('', self::TEST_SETTING_1_1_NAME);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be >0 indicating failure.
        $this->assertGreaterThan(0, $resultObj->exitCode);

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $this->assertStringContainsString('InvalidArgumentException', $resultObj->output);
    }

    //
    //*************************************************************************
    // Tests for nonexistent data.
    //*************************************************************************
    //
    public function test_Command_NonExistantSectionShouldYieldEmpty()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions('ConfigGetTest_Section_does_not_exist');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $expectedValue = self::NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_NonExistantSectionAndSettingShouldYieldEmpty()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions('ConfigGetTest_Section_does_not_exist', 'ConfigGetTest_Setting_does_not_exist');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $expectedValue = self::NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_NonExistantSettingShouldYieldEmpty()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION1_NAME, 'ConfigGetTest_Setting_does_not_exist');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $expectedValue = self::NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }
    //
    //*************************************************************************
    // Tests for existing data.
    //*************************************************************************
    //

    /**
     * Assumes default --format=json.
     */
    public function test_Command_GetSingleSettingFormatDefault()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $sectionName = self::TEST_SECTION1_NAME;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Built config with section=' . serialize($config->$sectionName)) . PHP_EOL;

        // Specifically set format='' (empty string) so we use the CLI default --format=json.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION1_NAME, self::TEST_SETTING_1_1_NAME, '');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        // With the default --format=json, the result should be JSON-encoded, meaning value=MyString gets wrapped in quotes like this: "MyString".
        $expectedValue = json_encode(self::TEST_SETTING_1_1_VALUE);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_GetSingleSettingFormatYaml()
    {
        $debug = true;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION1_NAME, self::TEST_SETTING_1_1_NAME, 'yaml');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        // With --format=yaml, a single value=MyString comes back with no quoting or brackets, e.g.: MyString.
        $expectedValue = self::TEST_SETTING_1_1_VALUE;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_GetSingleSettingFormatText()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION1_NAME, self::TEST_SETTING_1_1_NAME, 'text');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = self::TEST_SETTING_1_1_NAME . ': ' . self::TEST_SETTING_1_1_VALUE;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    /**
     * Assumes default --format=json.
     */
    public function test_Command_GetSectionFormatDefault()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        // Specifically set format='' (empty string) so we use the CLI default --format=json.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION1_NAME, false, '');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = json_encode((object) self::TEST_SETTING_1_ARR);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_GetSectionFormatYaml()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION1_NAME, false, 'yaml');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = trim(Yaml::dump(self::TEST_SETTING_1_ARR, 2, 2, true));
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_GetSectionWithArray()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_NAME_WITH_ARRAY);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $settingName = self::TEST_SETTING_NAME_OF_ARRAY;
        $expectedValue = json_encode((object) [$settingName => self::TEST_SETTING_VALUES_OF_ARRAY]);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_GetArraySettingFromSection()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_NAME_WITH_ARRAY, self::TEST_SETTING_NAME_OF_ARRAY);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = json_encode(self::TEST_SETTING_VALUES_OF_ARRAY);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function test_Command_CallWithMultipleSectionsReturnsLastSectionOnly()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $inputArr = [
            'command' => 'config:get',
            '--section' => self::TEST_SECTION_NAME_WITH_ARRAY,
            '--section' => self::TEST_SECTION1_NAME,
            '-vvv' => false,
        ];
        $exitCode = $this->applicationTester->run($inputArr);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $exitCode);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        $expectedValue = json_encode((object) self::TEST_SETTING_1_ARR);
        $this->assertEquals($expectedValue, $output);
    }
}
