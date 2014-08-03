<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests;

use Piwik\Plugins\API\Renderer\Json;

class JsonRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Json
     */
    private $jsonBuilder;

    public function setUp()
    {
        $this->jsonBuilder = new Json(array());
    }

    /**
     * Two dimensions standard array
     *
     * @group Core
     */
    public function testConvertMultiDimensionalStandardArrayToJson()
    {
        $input = array("firstElement",
                       array(
                           "firstElement",
                           "secondElement",
                       ),
                       "thirdElement");

        $expected = json_encode($input);

        $actual = $this->jsonBuilder->renderArray($input);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions associative array
     *
     * @group Core
     */
    public function testConvertMultiDimensionalAssociativeArrayToJson()
    {
        $input = array(
            "firstElement"  => "isFirst",
            "secondElement" => array(
                "firstElement"  => "isFirst",
                "secondElement" => "isSecond",
            ),
            "thirdElement"  => "isThird");

        $expected = json_encode($input);

        $actual = $this->jsonBuilder->renderArray($input);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions mixed array
     *
     * @group Core
     */
    public function testConvertMultiDimensionalMixedArrayToJson()
    {
        $input = array(
            "firstElement" => "isFirst",
            array(
                "firstElement",
                "secondElement",
            ),
            "thirdElement" => array(
                "firstElement"  => "isFirst",
                "secondElement" => "isSecond",
            )
        );

        $expected = json_encode($input);

        $actual = $this->jsonBuilder->renderArray($input);
        $this->assertEquals($expected, $actual);
    }
}
