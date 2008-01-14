<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Visualization
 */

require_once "Visualization/Chart.php";

/**
 * 
 * @package Piwik_Visualization
 *
 */
class Piwik_Visualization_ChartVerticalBar extends Piwik_Visualization_Chart
{
	protected $limit = 10;
		
	function customizeGraph()
	{
		$this->prepareData();
//		$this->title( 'Bar Chart', '{font-size: 20px;}' );
		$this->set_data( $this->arrayData );
		$this->set_x_labels( $this->arrayLabel );
		$this->set_x_label_style( 10, '#9933CC', 0, 2 );
		// and tick every second value:
		$this->set_x_axis_steps( 2 );
		$this->set_y_max( $this->maxData );
		$this->y_label_steps( 3 );
		
		$this->bar_filled( 50, '#9933CC', '#8010A0', 'visits', 10 );
		$this->set_tool_tip( '#x_label# <br>#val# #key# ' );
//		$this->set_y_legend( 'Open Flash Chart', 12, '#736AFF' );
	}
	
}