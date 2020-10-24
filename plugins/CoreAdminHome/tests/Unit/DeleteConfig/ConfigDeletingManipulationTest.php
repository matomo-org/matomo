<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Unit\Commands\DeleteConfig;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugins\CoreAdminHome\Commands\DeleteConfig\ConfigDeletingManipulation;
use Piwik\Plugins\CoreAdminHome\Commands\SetConfig\ConfigSettingManipulation;

// phpunit mocks can't return references, so we need a manual one
class DumbMockConfig extends Config
{
    /**
     * @var array
     */
    public $mockConfigData;

    public function __construct()
    {
        // empty
    }

    public function &__get($sectionName)
    {
        if (!isset($this->mockConfigData[$sectionName])) {
            $this->mockConfigData[$sectionName] = array();
        }

        $result =& $this->mockConfigData[$sectionName];
        return $result;
    }

    public function __set($sectionName, $section)
    {
        $this->mockConfigData[$sectionName] = $section;
    }
}

/**
 * @group CoreAdminHome
 * @group CoreAdminHome_Unit
 */
class ConfigDeletingManipulationTest extends TestCase
{
    /**
     * @var Config
     */
    private $mockConfig;

    /**
     * @var array
     */
    private $mockConfigData;

    public function setUp(): void
    {
        $this->mockConfig = new DumbMockConfig();
        $this->mockConfigData = array();

        foreach ($this->getTestConfig() as [$sectionName, $name, $value, $isArrayAppend]){
            $manipulation = new ConfigSettingManipulation($sectionName, $name, $value, $isArrayAppend);
            $manipulation->manipulate($this->mockConfig);
        }
    }

    public function getTestConfig(): array
    {
        return array(
            // normal assign (string, int, array, bool)
            array("Section", "config_setting", "stringvalue", false),
            array("Section", "config_setting_two", 25, false),
            array("Section", "config_setting_three", array('a', 'b'), false),
            array("Section", "config_setting_for", false, false),
        );
    }

    /**
     * @dataProvider getTestDataForMake
     *
     * @param $assignmentString
     * @param $expectedSectionName
     * @param $expectedSettingName
     * @param $expectedIsSectionDeletion
     * @param $expectedSettingPositionToDelete
     */
    public function test_make_CreatesCorrectManipulation($assignmentString, $expectedSectionName, $expectedSettingName, $expectedIsSectionDeletion, $expectedSettingPositionToDelete): void
    {
        $manipulation = ConfigDeletingManipulation::make($assignmentString);

        $this->assertEquals($expectedSectionName, $manipulation->getSectionName());
        $this->assertEquals($expectedSettingName, $manipulation->getName());
        $this->assertEquals($expectedIsSectionDeletion, $manipulation->isSectionDeletion());
        $this->assertEquals($expectedSettingPositionToDelete, $manipulation->getSettingPositionToDelete());
    }

    public function getTestDataForMake(): array
    {
        return array(
            // Setting Delete
            array("General.myconfig", "General", "myconfig", false, null),

            // Setting position delete
            array("General.myconfig[2]", "General", 'myconfig', false, 2),

            // Section delete
            array("General", "General", null, true, null),

        );
    }

    public function test_manipulate_ThrowIfSectionDoesntExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Trying to delete not existing config section [General]');

        $manipulation = new ConfigDeletingManipulation("General", null, true);
        $manipulation->manipulate($this->mockConfig);
    }

    public function test_manipulate_ThrowIfConfigArrayPositionDoesntExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Trying to delete not existing position in array setting [Section] config_setting_three[3].');

        $manipulation = new ConfigDeletingManipulation("Section", 'config_setting_three', false, 3);
        $manipulation->manipulate($this->mockConfig);
    }

    public function test_manipulate_ThrowIfConfigArrayDoesntExistForSettingPosition(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Trying to delete not existing config in array setting [Section] config_setting_two[3].');

        $manipulation = new ConfigDeletingManipulation("Section", 'config_setting_two', false, 3);
        $manipulation->manipulate($this->mockConfig);
    }

    public function test_manipulate_ThrowIfConfigSettingDoesntExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Trying to delete not existing config in array setting [Section] config_setting_five.');

        $manipulation = new ConfigDeletingManipulation("Section", 'config_setting_five');
        $manipulation->manipulate($this->mockConfig);
    }

    /**
     * @dataProvider getTestDataForManipulate
     *
     * @param $sectionName
     * @param $name
     * @param $isSectionDeletion
     * @param $settingPositionToDelete
     * @param $expectedConfig
     */
    public function test_manipulate_CorrectlyManipulatesConfig($sectionName, $name, $isSectionDeletion, $settingPositionToDelete, $expectedConfig): void
    {
        $manipulation = new ConfigDeletingManipulation($sectionName, $name, $isSectionDeletion, $settingPositionToDelete);
        $manipulation->manipulate($this->mockConfig);

        $this->assertEquals($expectedConfig, $this->mockConfig->mockConfigData);
    }

    /**
     * @return array[]
     */
    public function getTestDataForManipulate():array
    {
        return array(
            // Section delete (string, string, bool, int)
            array("Section", null, true, null, array("Section" => null)),
            // Setting delete
            array("Section", 'config_setting_two', false, null, array("Section" =>  array('config_setting' => "stringvalue", 'config_setting_three' => array('a', 'b'), 'config_setting_for' => false))),
            // Array setting delete
            array("Section", 'config_setting_three', false, null, array("Section" =>  array('config_setting' => "stringvalue", 'config_setting_two' => 25, 'config_setting_for' => false))),
            // Array setting position delete
            array("Section", 'config_setting_three', false, 1, array("Section" =>  array('config_setting' => "stringvalue", 'config_setting_two' => 25, 'config_setting_three' => array('a'), 'config_setting_for' => false))),
        );
    }
}
