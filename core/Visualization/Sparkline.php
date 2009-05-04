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
	 * Array with format: array( x, y, z, ... )
	 * @param array $data
	 */
	function setValues($data)
	{
		$this->values = $data;
	}
	
	static public function getWidth()
	{
		return 100;
	}
	
	static public function getHeight()
	{
		return 25;
	}
	
	function main()
	{
		$width = self::getWidth();
		$height = self::getHeight();
		
		$data = $this->values;
		$sparkline = new Sparkline_Line();
		$sparkline->SetColor('lineColor', 22, 44, 74); // dark blue
		$sparkline->SetColorHtml('red', '#FF7F7F');
		$sparkline->SetColorHtml('blue', '#55AAFF');
		$sparkline->SetColorHtml('green', '#75BF7C');
		
		$data = array_reverse($data);
		$min = $max = $last = null;
		$i = 0;
		foreach($this->values as $value)
		{
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
		$sparkline->setYMax($max[1] + 0.5); // the +0.5 seems to be mandatory to not lose some pixels when value = max
		$sparkline->SetPadding( 3, 0, 2, 0);
		$font = FONT_2;
		// the -0.5 is a hack as the sparkline samping rendering is obviously slightly bugged
		// (see also fix marked as //FIX FROM PIWIK in libs/sparkline/lib/Sparkline.php)
		$sparkline->SetFeaturePoint($min[0] -0.5,  $min[1],  'red', 5);
		$sparkline->SetFeaturePoint($max[0] -0.5,  $max[1],  'green', 5);
		$sparkline->SetFeaturePoint($last[0] -0.5, $last[1], 'blue', 5);
		$sparkline->SetLineSize(3); // for renderresampled, linesize is on virtual image
		$ratio = 1;
//		var_dump($min);var_dump($max);var_dump($lasts);exit;
		$sparkline->RenderResampled($width*$ratio, $height*$ratio);
		
		$this->sparkline = $sparkline;
	}
	
	function render()
	{
		$this->sparkline->Output();
	}
}
