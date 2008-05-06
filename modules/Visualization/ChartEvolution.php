<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ChartVerticalBar.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik_Visualization
 */

require_once "Visualization/Chart.php";

/**
 * Customize the Evolution chart style for the flash graph
 * 
 * @package Piwik_Visualization
 *
 */
class Piwik_Visualization_ChartEvolution extends Piwik_Visualization_Chart
{		
	function customizeGraph()
	{
		parent::customizeGraph();
		$this->prepareData();
		$this->set_y_max( $this->maxData );
		
		$line_1 = new line_hollow( 1, 3, '0x3357A0' );
		$line_1->key( 'visits', 10 );
		
		$i = 0;
		foreach($this->arrayData as $value)
		{
			// hack until we have proper date handling
			$spacePosition = strpos($this->arrayLabel[$i],' ');
			if($spacePosition === false)
			{
				$spacePosition = strlen($this->arrayLabel[$i]);
			}
			
			// generate the link on the dot, to the given day' statistics
			$link = Piwik_Url::getCurrentScriptName() 
							. Piwik_Url::getCurrentQueryStringWithParametersModified( array(
										'date' => substr($this->arrayLabel[$i],0,$spacePosition),
										'module' => 'Home',
										'action' => 'index',
										'viewDataTable' => null// we reset the viewDataTable parameter (useless in the link)
										));
			
			$line_1->add_link($value, $link );
			$i++;
		}
		$this->data_sets[] = $line_1;
		
		$this->set_x_labels( $this->arrayLabel );
		$this->area_hollow( 1, 3, 4,'0x3357A0',  ' visits', 10 );	
	}
}