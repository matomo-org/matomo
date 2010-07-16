<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_ExampleAPI
 */

/**
 * This is an example of a basic API file. Each plugin can have one public API.
 * Each public function in this class will be available to be called via the API.
 * Protected and private members will not be callable.
 * 
 * Functions can be called internally using the PHP objects directly, or via the 
 * Piwik Web APIs, using HTTP requests. For more information, check out:
 * http://dev.piwik.org/trac/wiki/API/CallingTechniques
 * 
 * Parameters are passed automatically from the GET request to the API functions.
 * 
 * Common API uses include: 
 * - requesting stats for a given date and period, for one or several websites
 * - creating, editing, deleting entities (Goals, Websites, Users)
 * - any logic that could be useful to a larger scope than the Controller (make a setting editable for example)
 * 
 * It is highly recommended that all the plugin logic is done inside API implementations, and the 
 * Controller and other objects would all call the API internally using, eg.
 *  Piwik_ExampleAPI_API::getInstance()->getSum(1, 2);
 * 
 * 
 * @package Piwik_ExampleAPI
 */
class Piwik_ExampleAPI_API
{
	static private $instance = null;

	/**
	 * Singleton
	 * @return Piwik_ExampleAPI_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	/**
	 * Get Piwik version
	 * @return string
	 */
	public function getPiwikVersion()
	{
		Piwik::checkUserHasSomeViewAccess();
		return Piwik_Version::VERSION;
	}

	/**
	 * Get Answer to Life
	 * @return integer
	 */
	public function getAnswerToLife()
	{
		return 42;
	}

	/**
	 * Returns a custom object.
	 * API format conversion will fail for this custom object. 
	 * If used internally, the data structure can be returned untouched by using
	 * the API parameter 'format=original'
	 * 
	 * @return Piwik_MagicObject Will return a standard Piwik error when called from the Web APIs
	 */
	public function getObject()
	{
		return new Piwik_MagicObject();
	}

	/**
	 * Sums two floats and returns the result.
	 * The paramaters are set automatically from the GET request
	 * when the API function is called. You can also use default values
	 * as shown in this example.
	 * 
	 * @param $a
	 * @param $b
	 * @return float
	 */
	public function getSum($a = 0, $b = 0)
	{
		return (float)($a + $b);
	}
	
	/**
	 * Returns null value
	 * 
	 * @return null 
	 */
	public function getNull()
	{
		return null;
	}

	/**
	 * Get array of descriptive text
	 * When called from the Web API, you see that simple arrays like this one
	 * are automatically converted in the various formats (xml, csv, etc.)
	 * 
	 * @return array
	 */
	public function getDescriptionArray()
	{
		return array('piwik','open source','web analytics','free', 'Strong message: Свободный Тибет');
	}

	/**
	 * Returns a custom data table.
	 * This data table will be converted to all available formats 
	 * when requested in the API request.
	 * 
	 * @return Piwik_DataTable
	 */
	public function getCompetitionDatatable()
	{
		$dataTable = new Piwik_DataTable();

		$row1 = new Piwik_DataTable_Row();
		$row1->setColumns( array('name' => 'piwik', 'license' => 'GPL'));
		
		// Rows Metadata is useful to store non stats data for example (logos, urls, etc.)
		// When printed out, they are simply merged with columns 
		$row1->setMetadata('logo', 'logo.png');
		$dataTable->addRow($row1);

		$dataTable->addRowFromSimpleArray( array('name' => 'google analytics', 'license' => 'commercial')  );

		return $dataTable;
	}

	/**
	 * Get more information on the Answer to Life...
	 * 
	 * @return string
	 */
	public function getMoreInformationAnswerToLife()
	{
		return "Check http://en.wikipedia.org/wiki/The_Answer_to_Life,_the_Universe,_and_Everything";
	}
	
	/**
	 * Returns a Multidimensional Array
	 * Only supported in JSON
	 * 
	 * @return array
	 */
	public function getMultiArray()
	{
		$return = array(
			'Limitation' => array(
				"Multi dimensional arrays is only supported by format=JSON", 
				"Known limitation"
			),
			'Second Dimension' => array( true, false, 1, 0, 152, 'test', array( 42 => 'end') ),
		);
		return $return;
	}
}

/**
 * Magic Object
 *
 * @package Piwik_ExamplePlugin
 */
class Piwik_MagicObject
{
	function Incredible() { return 'Incroyable'; }
	protected $wonderful = 'magnifique';
	public $great = 'formidable';
}
