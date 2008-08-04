<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ChartVerticalBar.php 459 2008-05-06 22:39:42Z matt $
 * 
 * @package Piwik_Visualization
 */

require_once "Visualization/Chart.php";

/**
 * 
 * Customize the Vertical bar chart style for the flash graph
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
		$this->set_data( $this->arrayData );
		$this->set_x_labels( $this->arrayLabel );
		$this->set_x_label_style( 12, $this->x_axis_colour, 0, 2, $this->bg_colour );
		$this->set_x_axis_steps( 2 );
		$this->set_y_max( $this->maxData );
		$this->y_label_steps( 2 );
		$this->bar_filled( 50, '#3B5AA9', '#063E7E', 'visits', 10 );
	}
	
}