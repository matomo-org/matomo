<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Json.php 516 2008-06-08 20:06:43Z matt $
 * 
 * @package Piwik_DataTable
 */

require_once "DataTable/Renderer/Php.php";
/**
 * JSON export. Using the php 5.2 feature json_encode.
 * Works with recursive DataTable (when a row can be associated with a subDataTable).
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_Json extends Piwik_DataTable_Renderer
{
	function __construct($table = null, $renderSubTables = null)
	{
		parent::__construct($table, $renderSubTables);
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}

	protected function renderTable($table)
	{
		$renderer = new Piwik_DataTable_Renderer_Php($table, $this->renderSubTables, $serialize = false);
		$array = $renderer->flatRender();
		
		if(!is_array($array))
		{
			$array = array('value' => $array);
		}
		$str = json_encode($array);
		
		if(($jsonCallback = Piwik_Common::getRequestVar('jsoncallback', false)) !== false)
		{
			if(preg_match('/^[0-9a-zA-Z]*$/', $jsonCallback) > 0)
			{
				$str = $jsonCallback . "(" . $str . ")";
			}
		}
		return $str;
	}
}