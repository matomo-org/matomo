<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * TSV export
 * 
 * Excel doesn't import CSV properly, it expects TAB separated values by default.
 * TSV is therefore the 'CSV' that is Excel compatible
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Renderer_Tsv extends Piwik_DataTable_Renderer_Csv
{
	function __construct()
	{
		parent::__construct();
		$this->setSeparator("\t");
	}
	
	function render()
	{
		return parent::render();
	}
}
