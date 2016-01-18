<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Test\Integration\Commands;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Ini\IniReader;
use Piwik\Plugins\Diagnostics\ConfigReader;
use Piwik\Plugins\ExampleSettingsPlugin\Settings;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * TODO: This could be a unit test if we could inject the ArchiveTableDao in the command
 * @group Diagnostics
 * @group Plugins
 */
class ConfigReaderTest extends IntegrationTestCase
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    public function setUp()
    {
        $settings = new GlobalSettingsProvider($this->configPath('global.ini.php'), $this->configPath('config.ini.php'), $this->configPath('common.config.ini.php'));
        $this->configReader = new ConfigReader($settings, new IniReader());
    }

    public function test_getConfigValuesFromFiles()
    {
        $fileConfig = $this->configReader->getConfigValuesFromFiles();

        $expected = array (
            'Category' =>
                array (
                    'key1' =>
                        array (
                            'value' => 'value_overwritten',
                            'description' => '',
                            'isCustomValue' => true,
                            'defaultValue' => 'value1',
                        ),
                    'key2' =>
                        array (
                            'value' => 'valueCommon',
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' => 'value2',
                        ),
                    'key3' =>
                        array (
                            'value' => '${@piwik(crash))}',
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' => NULL,
                        ),
                ),
            'CategoryOnlyInGlobalFile' =>
                array (
                    'key3' =>
                        array (
                            'value' => 'value3',
                            'description' => 'test comment',
                            'isCustomValue' => false,
                            'defaultValue' => 'value3',
                        ),
                    'key4' =>
                        array (
                            'value' => 'value4',
                            'description' => 'test comment 4',
                            'isCustomValue' => false,
                            'defaultValue' => 'value4',
                        ),
                ),
            'TestArray' =>
                array (
                    'installed' =>
                        array (
                            'value' =>
                                array (
                                    0 => 'plugin"1',
                                    1 => 'plugin2',
                                    2 => 'plugin3',
                                ),
                            'description' => 'test comment 2
with multiple lines',
                            'isCustomValue' => true,
                            'defaultValue' =>
                                array (
                                    0 => 'plugin1',
                                    1 => 'plugin4',
                                ),
                        ),
                ),
            'TestArrayOnlyInGlobalFile' =>
                array (
                    'my_array' =>
                        array (
                            'value' =>
                                array (
                                    0 => 'value1',
                                    1 => 'value2',
                                ),
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' =>
                                array (
                                    0 => 'value1',
                                    1 => 'value2',
                                ),
                        ),
                ),
            'GeneralSection' =>
                array (
                    'password' =>
                        array (
                            'value' => '******',
                            'description' => '',
                            'isCustomValue' => true,
                            'defaultValue' => NULL,
                        ),
                    'login' =>
                        array (
                            'value' => 'tes"t',
                            'description' => '',
                            'isCustomValue' => true,
                            'defaultValue' => NULL,
                        ),
                ),
            'TestOnlyInCommon' =>
                array (
                    'value' =>
                        array (
                            'value' => 'commonValue',
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' => NULL,
                        ),
                ),
            'Tracker' =>
                array (
                    'commonConfigTracker' =>
                        array (
                            'value' => 'commonConfigTrackerValue',
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' => NULL,
                        ),
                ),
        );
        $this->assertEquals($expected, $fileConfig);
    }

    public function test_addConfigValuesFromPluginSettings()
    {
        $settings = new Settings();

        $configValues = $this->configReader->addConfigValuesFromPluginSettings(array(), array($settings));

        $expected = array (
            'ExampleSettingsPlugin' =>
                array (
                    'metric' =>
                        array (
                            'value' => NULL,
                            'description' => 'Choose the metric that should be displayed in the browser tab',
                            'isCustomValue' => false,
                            'defaultValue' => 'nb_visits',
                        ),
                    'browsers' =>
                        array (
                            'value' => NULL,
                            'description' => 'The value will be only displayed in the following browsers',
                            'isCustomValue' => false,
                            'defaultValue' =>
                                array (
                                    0 => 'firefox',
                                    1 => 'chromium',
                                    2 => 'safari',
                                ),
                        ),
                    'description' =>
                        array (
                            'value' => NULL,
                            'description' => 'This description will be displayed next to the value',
                            'isCustomValue' => false,
                            'defaultValue' => 'This is the value: 
Another line',
                        ),
                    'password' =>
                        array (
                            'value' => NULL,
                            'description' => 'Password for the 3rd API where we fetch the value',
                            'isCustomValue' => false,
                            'defaultValue' => NULL,
                        ),
                ),
        );
        $this->assertEquals($expected, $configValues);
    }

    public function test_addConfigValuesFromPluginSettings_shouldAddDescriptionAndDefaultValueForExistingConfigValues()
    {
        $settings = new Settings();

        $existing = array(
            'ExampleSettingsPlugin' =>
                array (
                    'metric' =>
                        array (
                            'value' => NULL,
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' => null,
                        ),
                    )
        );

        $configValues = $this->configReader->addConfigValuesFromPluginSettings($existing, array($settings));

        $this->assertSame('Choose the metric that should be displayed in the browser tab', $configValues['ExampleSettingsPlugin']['metric']['description']);
        $this->assertSame('nb_visits', $configValues['ExampleSettingsPlugin']['metric']['defaultValue']);
    }

    public function test_addConfigValuesFromPluginSettings_shouldMaskValueIfTypeIsPassword()
    {
        $settings = new Settings();
        $settings->metric->uiControlType = Settings::CONTROL_PASSWORD;

        $existing = array(
            'ExampleSettingsPlugin' =>
                array (
                    'metric' =>
                        array (
                            'value' => 'test',
                            'description' => '',
                            'isCustomValue' => false,
                            'defaultValue' => null,
                        ),
                    )
        );

        $configValues = $this->configReader->addConfigValuesFromPluginSettings($existing, array($settings));

        $this->assertSame('******', $configValues['ExampleSettingsPlugin']['metric']['value']);
    }

    private function configPath($file)
    {
        return PIWIK_INCLUDE_PATH . '/tests/resources/Config/' . $file;
    }
}

AnalyzeArchiveTableTest::$fixture = new OneVisitorTwoVisits();