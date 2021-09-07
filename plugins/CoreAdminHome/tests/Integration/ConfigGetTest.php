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
 *   One test: cd /path/to/matomo/tests/PHPUnit &&  /path/to/matomo/vendor/bin/phpunit --filter testGetSectionWithArray /path/to/matomo/plugins/CoreAdminHome/tests/Integration/ConfigGetTest.php; cd ../..
 */
class ConfigGetTest extends ConsoleCommandTestCase
{
    /*
     * The text this command outputs when no matching value is found.
     */

    private const COMMAND = 'config:get';
    private const CLASS_NAME_SHORT = 'ConfigDeleteTest';
    private const MSG_NOTHING_FOUND = 'Nothing found';

    /*
     * Path to store the test config file. It should be deleted when done.
     */
    private const TEST_CONFIG_PATH = '/tmp/test.config.ini.php';
    // Section 1.
    private const TEST_SECTION_1_NAME = self::CLASS_NAME_SHORT . '_test_section_1';
    private const TEST_SETTING_1_1_NAME = self::CLASS_NAME_SHORT . '_test_setting_1';
    private const TEST_SETTING_1_1_VALUE = self::CLASS_NAME_SHORT . '_test_value_1';
    private const TEST_SETTING_1_2_NAME = self::CLASS_NAME_SHORT . '_test_setting_2';
    private const TEST_SETTING_1_2_VALUE = self::CLASS_NAME_SHORT . '_test_value_2';
    private const TEST_SETTING_1_SUMMARIZED = [
        self::TEST_SETTING_1_1_NAME => self::TEST_SETTING_1_1_VALUE,
        self::TEST_SETTING_1_2_NAME => self::TEST_SETTING_1_2_VALUE,
    ];
    // Section 2.
    private const TEST_SECTION_2_NAME = self::CLASS_NAME_SHORT . '_test_section_2';
    private const TEST_SETTING_2_1_NAME = self::CLASS_NAME_SHORT . '_array_setting';
    private const TEST_SETTING_2_1_VALUE_0 = self::CLASS_NAME_SHORT . '_arr_val_1';
    private const TEST_SETTING_2_1_VALUE_1 = self::CLASS_NAME_SHORT . '_arr_val_2';
    private const TEST_SETTING_2_1_VALUE_2 = self::CLASS_NAME_SHORT . '_arr_val_3';
    private const TEST_SETTING_2_1_VALUES = [self::TEST_SETTING_2_1_VALUE_0, self::TEST_SETTING_2_1_VALUE_1, self::TEST_SETTING_2_1_VALUE_2];

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
        $settingsProvider = new GlobalSettingsProvider(null, self::getTestConfigFilePath());
        $config = new Config($settingsProvider);

        // Add the first section.
        $sectionName = self::TEST_SECTION_1_NAME;
        $config->$sectionName[self::TEST_SETTING_1_1_NAME] = self::TEST_SETTING_1_1_VALUE;
        $config->$sectionName[self::TEST_SETTING_1_2_NAME] = self::TEST_SETTING_1_2_VALUE;

        // Add a second section so we are testing that we do not accidentally return it.
        $sectionName = self::TEST_SECTION_1_NAME . '_second_section';
        // Add a setting with the same name as in section #1 but with a random int value.
        $config->$sectionName[self::TEST_SETTING_1_1_NAME] = random_int(PHP_INT_MIN, PHP_INT_MAX);
        // Add another setting to the same section with some bogus content.
        $config->$sectionName[self::TEST_SETTING_1_2_NAME . '_another'] = '127.0.0.1';

        // Add an array value like section=PluginsInstalled; setting=PluginsInstalled[].
        $sectionName = self::TEST_SECTION_2_NAME;
        // Add some values to the setting array.
        $config->$sectionName[self::TEST_SETTING_2_1_NAME] = self::TEST_SETTING_2_1_VALUES;

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
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . str_repeat('-', 80) . PHP_EOL . __FUNCTION__ . "::Started with \$sectionName=$sectionName; \$settingName=$settingName; \$format=$format") . PHP_EOL;

        $inputArr = [
            'command' => self::COMMAND,
            '--section' => $sectionName,
            '--key' => $settingName,
            '-vvv' => false,
        ];
        // To allow using default format, only add the format option if specified.
        if (!empty($format)) {
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
            'command' => self::COMMAND,
            '-vvv' => false,
            'argument' => $sectionName . (empty($settingName) ? '' : ".$settingName"),
        ];
        // To allow using default format, only add the format option if specified.
        if (!empty($format)) {
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

    public function testNoArgsShouldYieldError()
    {
        $debug = false;

        $inputArr = [
            'command' => self::COMMAND,
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

    public function testEmptyArgsShouldYieldError()
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

    public function testEmptyOptionsShouldYieldError()
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

    public function testSetArgsAndOptionsShouldYieldError()
    {
        $debug = false;
        $inputArr = [
            'command' => self::COMMAND,
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
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        $this->assertStringContainsString('InvalidArgumentException', $output);
    }

    public function testEmptySectionShouldYieldError()
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
    public function testNonExistentSectionShouldYieldEmpty()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::CLASS_NAME_SHORT . '_Section_does_not_exist');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testNonExistentSectionAndSettingShouldYieldEmpty()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::CLASS_NAME_SHORT . '_Section_does_not_exist', self::CLASS_NAME_SHORT . '_Setting_does_not_exist');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $expectedValue = self::MSG_NOTHING_FOUND;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testNonExistentSettingShouldYieldEmpty()
    {
        $debug = false;

        // Pass empty section name.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, self::CLASS_NAME_SHORT . '_Setting_does_not_exist');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        $expectedValue = self::MSG_NOTHING_FOUND;
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
    public function testGetSingleSettingFormatDefault()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        // Specifically set format='' (empty string) so we use the CLI default --format=json.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_1_NAME, '');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        // With the default --format=json, the result should be JSON-encoded, meaning value=MyString gets wrapped in quotes like this: "MyString".
        $expectedValue = json_encode(self::TEST_SETTING_1_1_VALUE);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testGetSingleSettingFormatYaml()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_1_NAME, 'yaml');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        // With --format=yaml, a single value=MyString comes back with no quoting or brackets, e.g.: MyString.
        $expectedValue = self::TEST_SETTING_1_1_VALUE;
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testGetSingleSettingFormatText()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, self::TEST_SETTING_1_1_NAME, 'text');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $resultArr = explode(PHP_EOL, $resultObj->output);
        $resultArrLineCounter = 0;

        $expectedValue = self::TEST_SECTION_1_NAME . '.' . self::TEST_SETTING_1_1_NAME . ' = ' . self::TEST_SETTING_1_1_VALUE;
        $this->assertEquals($expectedValue, $resultArr[$resultArrLineCounter++]);
    }

    /**
     * Assumes default --format=json.
     */
    public function testGetSectionFormatDefault()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        // Specifically set format='' (empty string) so we use the CLI default --format=json.
        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, false, '');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = json_encode((object) self::TEST_SETTING_1_SUMMARIZED);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testGetSectionFormatYaml()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, false, 'yaml');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = trim(Yaml::dump(self::TEST_SETTING_1_SUMMARIZED, 2, 2, true));
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testGetSectionNoArrayFormatText()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, false, 'text');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $resultArr = explode(PHP_EOL, $resultObj->output);
        $resultArrLineCounter = 0;
        $this->assertStringContainsString('[' . self::TEST_SECTION_1_NAME . ']', $resultArr[$resultArrLineCounter++]);
        $this->assertStringContainsString('--', $resultArr[$resultArrLineCounter++]);
        $this->assertStringContainsString(self::TEST_SETTING_1_1_NAME . ' = ' . self::TEST_SETTING_1_1_VALUE, $resultArr[$resultArrLineCounter++]);
        $this->assertStringContainsString(self::TEST_SETTING_1_2_NAME . ' = ' . self::TEST_SETTING_1_2_VALUE, $resultArr[$resultArrLineCounter++]);
    }

    public function testGetSectionWithArrayFormatText()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_1_NAME, false, 'text');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $resultArr = explode(PHP_EOL, $resultObj->output);
        $resultArrLineCounter = 0;
        $this->assertStringContainsString('[' . self::TEST_SECTION_1_NAME . ']', $resultArr[$resultArrLineCounter++]);
        $this->assertStringContainsString('--', $resultArr[$resultArrLineCounter++]);
        $this->assertStringContainsString(self::TEST_SETTING_1_1_NAME . ' = ' . self::TEST_SETTING_1_1_VALUE, $resultArr[$resultArrLineCounter++]);
        $this->assertStringContainsString(self::TEST_SETTING_1_2_NAME . ' = ' . self::TEST_SETTING_1_2_VALUE, $resultArr[$resultArrLineCounter++]);
    }

    public function testGetSectionWithArray()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_2_NAME);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $settingName = self::TEST_SETTING_2_1_NAME;
        $expectedValue = json_encode((object) [$settingName => self::TEST_SETTING_2_1_VALUES]);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testGetArraySettingFromSection()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = json_encode(self::TEST_SETTING_2_1_VALUES);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testGetArraySettingWithBrackets()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $resultObj = $this->runCommandWithOptions(self::TEST_SECTION_2_NAME, self::TEST_SETTING_2_1_NAME . '[]');
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $resultObj->output) . PHP_EOL;

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $resultObj->exitCode, $this->getCommandDisplayOutputErrorMessage());

        $expectedValue = json_encode(self::TEST_SETTING_2_1_VALUES);
        $this->assertEquals($expectedValue, $resultObj->output);
    }

    public function testCallWithMultipleSectionsReturnsLastSectionOnly()
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started') . PHP_EOL;

        $inputArr = [
            'command' => self::COMMAND,
            '--section' => self::TEST_SECTION_2_NAME,
            '--section' => self::TEST_SECTION_1_NAME,
            '-vvv' => false,
        ];
        $exitCode = $this->applicationTester->run($inputArr);

        // The CLI error code should be 0 indicating success.
        $this->assertEquals(0, $exitCode);

        // Pass true to getDisplay(true) to normalize line endings, then trim() bc CLI adds an \ automatically.
        $output = trim($this->applicationTester->getDisplay(true));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got command output=' . $output) . PHP_EOL;

        $expectedValue = json_encode((object) self::TEST_SETTING_1_SUMMARIZED);
        $this->assertEquals($expectedValue, $output);
    }
}
