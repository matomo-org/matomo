<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Unit\Commands\SetConfig;

use Piwik\Config;
use Piwik\Plugins\CoreAdminHome\Commands\SetConfig\ConfigSettingManipulation;

// phpunit mocks can't return references, so we need a manual one
class DumbMockConfig extends \Piwik\Config
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
class ConfigSettingManipulationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $mockConfig;

    public function setUp(): void
    {
        $this->mockConfig = new DumbMockConfig();
    }

    /**
     * @dataProvider getTestDataForMake
     */
    public function test_make_CreatesCorrectManipulation(
        $assignmentString,
        $expectedSectionName,
        $expectedSettingName,
        $expectedSettingValue,
        $expectedIsArrayAppend
    ) {
        $manipulation = ConfigSettingManipulation::make($assignmentString);

        $this->assertEquals($expectedSectionName, $manipulation->getSectionName());
        $this->assertEquals($expectedSettingName, $manipulation->getName());
        $this->assertEquals($expectedSettingValue, $manipulation->getValue());
        $this->assertEquals($expectedIsArrayAppend, $manipulation->isArrayAppend());
    }

    public function getTestDataForMake()
    {
        return array(
            // normal assign
            array("General.myconfig=0", "General", "myconfig", 0, false),

            // array append
            array("General.myconfig444[]=5", "General", "myconfig444", 5, true),

            // assign array
            array("1General1.2config2=[\"abc\",\"def\"]", "1General1", "2config2", array('abc', 'def'), false),

            // assign string
            array("MySection.value=\"ghi\"", "MySection", "value", "ghi", false),

            // assign boolean
            array("MySection.value=false", "MySection", "value", false, false),
            array("MySection.value=true", "MySection", "value", true, false),
        );
    }

    /**
     * @dataProvider getFailureTestDataForMake
     */
    public function test_make_ThrowsWhenInvalidAssignmentStringSupplied($assignmentString)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid assignment string');

        ConfigSettingManipulation::make($assignmentString);
    }

    public function getFailureTestDataForMake()
    {
        return array(
            array("General&.value=1"),
            array("General.val&*ue=12"),
            array("General.value=[notjson]"),
            array("General.value=notjson"),
            array("General.array[abc]=\"def\""),
        );
    }

    public function test_manipulate_ThrowsIfAppendingNonArraySetting()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Trying to append to non-array setting value');

        $this->mockConfig->mockConfigData['General']['config'] = "5";

        $manipulation = new ConfigSettingManipulation("General", "config", "10", true);
        $manipulation->manipulate($this->mockConfig);
    }

    public function test_manipulate_ThrowsIfAssigningNonArrayValue_ToArraySetting()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Trying to set non-array value to array setting');

        $this->mockConfig->mockConfigData['General']['config'] = array("5");

        $manipulation = new ConfigSettingManipulation("General", "config", "10", false);
        $manipulation->manipulate($this->mockConfig);
    }

    /**
     * @dataProvider getTestDataForManipulate
     */
    public function test_manipulate_CorrectlyManipulatesConfig($sectionName, $name, $value, $isArrayAppend, $expectedConfig)
    {
        $manipulation = new ConfigSettingManipulation($sectionName, $name, $value, $isArrayAppend);
        $manipulation->manipulate($this->mockConfig);

        $this->assertEquals($expectedConfig, $this->mockConfig->mockConfigData);
    }

    public function getTestDataForManipulate()
    {
        return array(
            // normal assign (string, int, array, bool)
            array("Section", "config_setting", "stringvalue", false, array("Section" => array("config_setting" => "stringvalue"))),
            array("Section", "config_setting", 25, false, array("Section" => array("config_setting" => 25))),
            array("Section", "config_setting", array('a' => 'b'), false, array("Section" => array("config_setting" => array('a' => 'b')))),
            array("Section", "config_setting", false, false, array("Section" => array("config_setting" => false))),

            // array append
            array("Section", "config_setting", "value", true, array("Section" => array("config_setting" => array('value')))),
            array("Section", "config_setting", array(1,2), true, array("Section" => array("config_setting" => array(array(1,2))))),
        );
    }
}
