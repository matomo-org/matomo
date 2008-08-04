<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: OpenFlashChart.php 386 2008-03-18 19:27:54Z julien $
 *
 * @package Piwik_Visualization
 */

require_once 'sparkline/lib/Sparkline_Line.php';


/**
 * Renders a sparkline image given a PHP data array.
 * Using the Sparkline PHP Graphing Library sparkline.org 
 * 
 * @package Piwik_Visualization
 */
class Piwik_Visualization_Sparkline implements Piwik_iView
{
	/**
	 * Sets data. Must have format: array( array('value' => X),array('value' =>Y ), ...)
	 *
	 * @param array $data
	 */
	function setData($data)
	{
		$this->data = $data;
	}
	
	
	function main()
	{
		$data = $this->data;
		$sparkline = new Sparkline_Line();
		
		$sparkline->SetColor('lineColor', 22,44,74); // dark blue
		$sparkline->SetColorHtml('red', '#FF7F7F');
		$sparkline->SetColorHtml('blue', '#55AAFF');
		$sparkline->SetColorHtml('green', '#75BF7C');
//		$sparkline->SetDebugLevel(DEBUG_NONE);
//		$sparkline->SetDebugLevel(DEBUG_ERROR | DEBUG_WARNING | DEBUG_STATS | DEBUG_CALLS | DEBUG_DRAW, 'log.txt');
		
		$data = array_reverse($data);
		$min = $max= $last = null;
		$i = 0;
		
		foreach($this->data as $row)
		{
			$value = $row['value'];
					
			$sparkline->SetData($i, $value);
			if(	null == $min || $value <= $min[1])
			{
				$min = array($i, $value);
			}
		
			if(null == $max || $value >= $max[1]) 
			{
				$max = array($i, $value);
			}
		
			$last = array($i, $value);
			
			$i++;			
		}
		$sparkline->SetYMin(0);
		$sparkline->SetPadding(2); // setpadding is additive
		$sparkline->SetPadding(0,//13,//font height 
					3, //4 * (strlen("$last[1]")), 
					0, //imagefontheight(FONT_2), 
					0);
		$font = FONT_2;
		$sparkline->SetFeaturePoint($min[0]-1,$min[1],'red', 5);
		$sparkline->SetFeaturePoint($max[0]-1,$max[1],  'green', 5);
		$sparkline->SetFeaturePoint($last[0]-1, $last[1], 'blue',5);
		$sparkline->SetLineSize(3); // for renderresampled, linesize is on virtual image
		$sparkline->RenderResampled(100, 20, 'lineColor');
		
		$this->sparkline = $sparkline;
	}
	
	function render()
	{
		$this->sparkline->Output();
	}
}