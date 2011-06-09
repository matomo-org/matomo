<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

class Test_Piwik_API_ResponseBuilder extends UnitTestCase
{
	function test_convertMultiDimensionalArrayToJson()
	{
		// Two dimensions standard array
		$input = array( "firstElement",
						array(
							"firstElement",
							"secondElement",
						),
						"thirdElement");
		$this->assertEqual(Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToJson($input), json_encode($input));

		// Two dimensions associative array
		$input = array(
					"firstElement" => "isFirst",
					"secondElement" => 	array(
											"firstElement" => "isFirst",
											"secondElement" => "isSecond",
										),
					"thirdElement" => "isThird");
		$this->assertEqual(Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToJson($input), json_encode($input));

		// Two dimensions mixed array
		$input = array(
					"firstElement" => "isFirst",
					array(
						"firstElement",
						"secondElement",
					),
					"thirdElement" => 	array(
											"firstElement" => "isFirst",
											"secondElement" => "isSecond",
										)
		);
		$this->assertEqual(Piwik_API_ResponseBuilder::convertMultiDimensionalArrayToJson($input), json_encode($input));
	}
}
