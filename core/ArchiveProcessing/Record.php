<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ArchiveProcessing
 */

/**
 * A Record is a tuple (name, value) to be saved in the database.
 * At its creation, the record registers itself to the RecordManager. 
 * The record will then be automatically saved in the DB once the Archiving process is finished. 
 * 
 * We have two record types available:
 * - numeric ; the value will be saved as float in the DB.
 * 	 It should be used for INTEGER, FLOAT
 * - blob ; the value will be saved in a binary field in the DB
 * 	 It should be used for all the other types: PHP variables, STRING, serialized OBJECTS or ARRAYS, etc.
 * 
 * @package Piwik_ArchiveProcessing
 * @subpackage Piwik_ArchiveProcessing_Record
 */
abstract class Piwik_ArchiveProcessing_Record
{
	public $name;
	public $value;
	
	function __construct( $name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	function __destruct()
	{
		destroy($this->value);
	}
}



