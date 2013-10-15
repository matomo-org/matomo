<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\API\ResponseBuilder;

class API_ResponseBuilderTest extends PHPUnit_Framework_TestCase
{
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
        $actual = ResponseBuilder::convertMultiDimensionalArrayToJson($input);
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
        $actual = ResponseBuilder::convertMultiDimensionalArrayToJson($input);
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
        $actual = ResponseBuilder::convertMultiDimensionalArrayToJson($input);
        $this->assertEquals($expected, $actual);
    }
}
