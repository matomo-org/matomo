<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Html.php 180 2008-01-17 16:32:37Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * RSS Feed. 
 * The RSS renderer can be used only on Piwik_DataTable_Array that are arrays of Piwik_DataTable.
 * A RSS feed contains one dataTable per element in the Piwik_DataTable_Array.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_Rss extends Piwik_DataTable_Renderer
{
	function __construct($table = null)
	{
		parent::__construct($table);
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	protected function renderTable($table)
	{
		if(!($table instanceof Piwik_DataTable_Array)
			|| $table->getKeyName() != 'date')
		{
			throw new Exception("RSS Feed only used on Piwik_DataTable_Array with keyName = 'date'");
		}
		
		$idSite = Piwik_Common::getRequestVar('idSite', 1);
		$period = Piwik_Common::getRequestVar('period');
		$currentUrl = Piwik_Url::getCurrentUrlWithoutFileName();
		
		$piwikUrl = $currentUrl . "?module=Home&action=index&idSite=" . $idSite . "&period=" . $period;
		
		$out = "";
		$moreRecentFirst = array_reverse($table->getArray(), true);
		foreach($moreRecentFirst as $date => $subtable )
		{
			$timestamp = $table->metadata[$date]['timestamp'];
			$site = $table->metadata[$date]['site'];
	
			$pudDate = date('r', $timestamp);
			$dateUrl = date('Y-m-d', $timestamp);
			$thisPiwikUrl = htmlentities($piwikUrl . "&date=$dateUrl");
			$siteName = $site->getName();
			$title = $siteName . " on ". $date;
			
			$out .= "\t<item>
		<pubDate>$pudDate</pubDate>
		<guid>$thisPiwikUrl</guid>
		<link>$thisPiwikUrl</link>
		<title>$title</title>
		<author>http://piwik.org</author>
		<description>";	
			
			$out .= htmlspecialchars( $this->renderDataTable($subtable) );
			$out .= "</description>\n\t</item>\n";
		}
		
		$header = $this->getRssHeader();
		$footer = $this->getRssFooter();
		
		return $this->output( $header . $out . $footer);
	}
	protected function output($str)
	{
		@header("Content-Type: text/xml;charset=utf-8");
		return $str;
	}
	protected function getRssFooter()
	{
		return "\t</channel>\n</rss>";
	}
	protected function getRssHeader()
	{
		$generationDate = date('r');
		$header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<rss version=\"2.0\">
  <channel>
    <title>piwik statistics - RSS</title>
    <link>http://piwik.org</link>
    <description>Piwik RSS feed</description>
    <pubDate>$generationDate</pubDate>
    <generator>piwik</generator>
    <language>en</language>
    <lastBuildDate>$generationDate</lastBuildDate>";
    	return $header;
	}
	
	protected function renderDataTable($table)
	{
	
		if($table->getRowsCount() == 0)
		{
			return "<b><i>Empty table</i></b> <br>\n";
		}
		if($table instanceof Piwik_DataTable_Simple 
			&& $table->getRowsCount() ==1)
		{
			$table->deleteColumn('label');
		}
		
		$i = 1;		
		$tableStructure = array();
		
		/*
		 * table = array
		 * ROW1 = col1 | col2 | col3 | metadata | idSubTable
		 * ROW2 = col1 | col2 (no value but appears) | col3 | metadata | idSubTable
		 * 		subtable here
		 */
		$allColumns = array();
		foreach($table->getRows() as $row)
		{
			foreach($row->getColumns() as $column => $value)
			{
				$allColumns[$column] = true;
				$tableStructure[$i][$column] = $value;
			}
			$i++;
		}
		$html = "\n";
		$html .= "<table border=1 width=70%>";
		$html .= "\n<tr>";
		foreach($allColumns as $name => $toDisplay)
		{
			if($toDisplay !== false)
			{
				$html .= "\n\t<td><b>$name</b></td>";
			}
		}
		$html .= "\n</tr>";
		$colspan = count($allColumns);
		
		foreach($tableStructure as $row)
		{
			$html .= "\n\n<tr>";
			foreach($allColumns as $name => $toDisplay)
			{
				if($toDisplay !== false)
				{
					$value = "-";
					if(isset($row[$name]))
					{
						$value = urldecode($row[$name]);
					}
					
					$html .= "\n\t<td>$value</td>";
				}
			}
			$html .= "</tr>";
			
		}
		$html .= "\n\n</table>";
		return $html;
	}
}

