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
		parent::customizeGraph();
		$this->prepareData();
//		$this->set_x_labels( $this->arrayLabel );
//		$this->area_hollow( 1, 3, 4,'0x3357A0',  ' visits', 10 );
//		
		$this->set_data( $this->arrayData );
		$this->set_x_labels( $this->arrayLabel );
		$this->set_x_label_style( 12, $this->x_axis_colour, 0, 2, $this->bg_colour );
		
		$this->set_x_axis_steps( 2 );
		$this->set_y_max( $this->maxData );
		$this->y_label_steps( 3 );
		
		$this->bar_filled( 50, '#3B5AA9', '#063E7E', 'visits', 10 );
//		$this->set_y_legend( 'Open Flash Chart', 12, '#736AFF' );
	}
	
}