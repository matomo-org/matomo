<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ReplaceColumnNames.php 482 2008-05-18 17:22:35Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_ReplaceSummaryRowLabel extends Piwik_DataTable_Filter
{
	public function __construct( $table, $newLabel = null)
	{
		parent::__construct($table);
		if(is_null($newLabel))
		{
			$newLabel = Piwik_Translate('General_Others');
		}
		$this->newLabel = $newLabel;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $row)
		{
			if($row->getColumn('label') === Piwik_DataTable::LABEL_SUMMARY_ROW)
			{
				$row->setColumn('label', $this->newLabel);
				break;
			}
		}
	}
}

