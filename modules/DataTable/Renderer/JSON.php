<?php

/**
 * JSON export. Using the php 5.2 feature json_encode
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
require_once "DataTable/Renderer/PHP.php";
class Piwik_DataTable_Renderer_JSON extends Piwik_DataTable_Renderer
{
	function __construct($table = null)
	{
		parent::__construct($table);
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	function renderTable($table)
	{
		$renderer = new Piwik_DataTable_Renderer_PHP($table, $serialize = false);
		$array = $renderer->render();
		$str = json_encode($array);
		return $str;
	}
}