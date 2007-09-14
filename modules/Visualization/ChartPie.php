<?php
require_once "Visualization/Chart.php";
class Piwik_Visualization_ChartPie extends Piwik_Visualization_Chart
{
	function getDefaultLimit()
	{
		return 5;
	}
	function customizeGraph()
	{
		$this->prepareData();		
//		$this->title( 'PIE Chart', '{font-size: 20px;}' );
		
		//
		$this->pie(60,'#505050','#000000');
		//
		// pass in two arrays, one of data, the other data labels
		//
		$this->pie_values( $this->arrayData, $this->arrayLabel );
		//
		// Colours for each slice, in this case some of the colours
		// will be re-used (3 colurs for 5 slices means the last two
		// slices will have colours colour[0] and colour[1]):
		//
		$this->pie_slice_colours( array('#d01f3c','#356aa0','#C79810') );
		

		$this->set_tool_tip( '#x_label# <br>#val# visits ' );
		
	}
	
}