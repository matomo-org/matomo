<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Record.php 180 2008-01-17 16:32:37Z matt $
 * 
 * @package Piwik_ArchiveProcessing
 */

/**
 * Blob record.
 * Example: $record = new Piwik_ArchiveProcessing_Record_Blob('visitor_names', serialize(array('piwik-fan', 'php', 'stevie-vibes')));
 * The value will be compressed before being saved in the DB.
 * 
 * @package Piwik_ArchiveProcessing
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Blob extends Piwik_ArchiveProcessing_Record
{
	public $name;
	public $value;
	function __construct( $name, $value)
	{
		$value = gzcompress($value);
		parent::__construct( $name, $value );
	}
	public function __toString()
	{
		return $this->name ." = BLOB";//". gzuncompress($this->value);
	}
}
