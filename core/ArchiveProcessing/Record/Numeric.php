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
 * Numeric record.
 * Example: $record = new Piwik_ArchiveProcessing_Record_Numeric('nb_visitors_live', 15);
 * 
 * @package Piwik_ArchiveProcessing
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Numeric extends Piwik_ArchiveProcessing_Record
{	
	function __construct( $name, $value)
	{
		parent::__construct( $name, $value );
	}
	
	public function __toString()
	{
		return $this->name ." = ". $this->value;
	}
}
