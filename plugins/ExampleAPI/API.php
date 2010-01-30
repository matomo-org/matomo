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
 * ExampleAPI API
 *
 * <p><b>HOW TO VIEW THE API IN ACTION</b></p>
 * <p>Go to the API page in the Piwik user interface
 * and try the API of the plugin ExampleAPI</p>
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
	static public function getPiwikVersion()
	{
		Piwik::checkUserHasSomeViewAccess();
		return Piwik_Version::VERSION;
	}

	/**
	 * Get Answer to Life
	 * @return integer
	 */
	static public function getAnswerToLife()
	{
		return 42;
	}

	/**
	 * Get Golden Ratio
	 * @return float
	 */
	static public function getGoldenRatio()
	{
		//http://en.wikipedia.org/wiki/Golden_ratio
		return 1.618033988749894848204586834365;
	}

	/**
	 * Get object
	 * @return Piwik_MagicObject
	 */
	static public function getObject()
	{
		return new Piwik_MagicObject();
	}

	/**
	 * Get null
	 * @return null
	 */
	static public function getNull()
	{
		return null;
	}

	/**
	 * Get array of descriptive text
	 * @return array
	 */
	static public function getDescriptionArray()
	{
		return array('piwik','open source','web analytics','free');
	}

	/**
	 * Get data table
	 * @return Piwik_DataTable
	 */
	static public function getCompetitionDatatable()
	{
		$dataTable = new Piwik_DataTable();

		$row1 = new Piwik_DataTable_Row();
		$row1->setColumns( array('name' => 'piwik', 'license' => 'GPL'));
		$dataTable->addRow($row1);

		$dataTable->addRowFromSimpleArray( array('name' => 'google analytics', 'license' => 'commercial')  );

		return $dataTable;
	}

	/**
	 * Get more information on the Answer to Life...
	 * @return string
	 */
	static public function getMoreInformationAnswerToLife()
	{
		return "Check http://en.wikipedia.org/wiki/The_Answer_to_Life,_the_Universe,_and_Everything";
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
