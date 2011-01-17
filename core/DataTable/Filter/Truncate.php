<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Truncate extends Piwik_DataTable_Filter
{	
	public function __construct( $table, $truncateAfter)
	{
		parent::__construct($table);
		$this->truncateAfter = $truncateAfter;
		$this->filter($table);
	}	
	
	protected function filter($table)
	{
		$table->filter('AddSummaryRow', array($this->truncateAfter));
		$table->filter('ReplaceSummaryRowLabel');
	}
}
