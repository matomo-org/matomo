<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class API_ResponseBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Two dimensions standard array
     *
     * @group Core
     * @group API
     * @group API_ResponseBuilder
     */
    public function testConvertMultiDimensionalStandardArrayToJson()
    {
        $input = array( "firstElement",
                        array(
                            "firstElement",
                            "secondElement",
                        ),
                        "thirdElement");

        $expected = json_encode($input);
        $actual   = Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToJson($input);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions associative array
     *
     * @group Core
     * @group API
     * @group API_ResponseBuilder
     */
    public function testConvertMultiDimensionalAssociativeArrayToJson()
    {
        $input = array(
                    "firstElement" => "isFirst",
                    "secondElement" =>     array(
                                            "firstElement" => "isFirst",
                                            "secondElement" => "isSecond",
                                        ),
                    "thirdElement" => "isThird");

        $expected = json_encode($input);
        $actual   = Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToJson($input);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions mixed array
     *
     * @group Core
     * @group API
     * @group API_ResponseBuilder
     */
    public function testConvertMultiDimensionalMixedArrayToJson()
    {
        $input = array(
                    "firstElement" => "isFirst",
                    array(
                        "firstElement",
                        "secondElement",
                    ),
                    "thirdElement" =>     array(
                                            "firstElement" => "isFirst",
                                            "secondElement" => "isSecond",
                                        )
        );

        $expected = json_encode($input);
        $actual   = Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToJson($input);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions standard array
     *
     * @group Core
     * @group API
     * @group API_ResponseBuilder
     */
    public function testConvertMultiDimensionalStandardArrayToXML()
    {
        $input = array( "firstElement",
                        array(
                            "firstElement",
                            "secondElement",
                        ),
                        "thirdElement");

        $expected = '<row>firstElement</row><row><row>firstElement</row><row>secondElement</row></row><row>thirdElement</row>';
        $actual   = preg_replace("/[\t\n]+/", '', Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToXml($input));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions associative array
     *
     * @group Core
     * @group API
     * @group API_ResponseBuilder
     */
    public function testConvertMultiDimensionalAssociativeArrayToXML()
    {
        $input = array(
                    "firstElement" => "isFirst",
                    "secondElement" =>     array(
                                            "firstElement" => "isFirst",
                                            "secondElement" => "isSecond",
                                        ),
                    "thirdElement" => "isThird");

        $expected = '<firstElement>isFirst</firstElement><secondElement><firstElement>isFirst</firstElement><secondElement>isSecond</secondElement></secondElement><thirdElement>isThird</thirdElement>';
        $actual   = preg_replace("/[\t\n]+/", '', Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToXml($input));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Two dimensions mixed array
     *
     * @group Core
     * @group API
     * @group API_ResponseBuilder
     */
    public function testConvertMultiDimensionalMixedArrayToXML()
    {
        $input = array(
                    "firstElement" => "isFirst",
                    array(
                        "firstElement",
                        "secondElement",
                    ),
                    "thirdElement" =>     array(
                                            "firstElement" => "isFirst",
                                            "secondElement" => "isSecond",
                                        )
        );

        $expected = '<firstElement>isFirst</firstElement><row><row>firstElement</row><row>secondElement</row></row><thirdElement><firstElement>isFirst</firstElement><secondElement>isSecond</secondElement></thirdElement>';
        $actual   = preg_replace("/[\t\n]+/", '', Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToXml($input));
        $this->assertEquals($expected, $actual);
    }
}
