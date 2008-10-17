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
 */
class Piwik_Visualization_ChartEvolution extends Piwik_Visualization_Chart
{
	function customizeGraph()
	{
		parent::customizeGraph();
		//$this->prepareData();

		$colors = array(
			"0x3357A0",
			"0x9933CC",
			"0xCC3399",			
			"0x80a033",
			"0xFD9816",
			"0x246AD2",
			"0xFD16EA",
			"0x49C100",
			);

		// first row in array contains line labels (legend)		
		$legendLabels = array_shift($this->dataGraph);

		$line = array();

		// define labels
		foreach($legendLabels as $nbLabel => $labelName)
		{
			$line[$nbLabel] = new line_hollow( 1, 3, $colors[$nbLabel] );
			$line[$nbLabel]->key( $labelName, 10 );
		}
		
		$maxData = 0;		
		$xLabels = array();		
		$cnt = count($this->dataGraph);
		
		// loop over data
		foreach($this->dataGraph as $values)
		{
			// add x axis value (label)
			array_push($xLabels, $values['label']);
			
			// loop over values for all lines (y axis values)		
			for($j = 0; $j < count($legendLabels); $j++)
			{
				// get the y axis value for line $j
				$dotValue = $values['value'.$j];
				
				// find maximum y axis value 
				if(  $dotValue > $maxData )
				{
					$maxData = $dotValue;
				}

				$link = null;
				if($this->isLinkEnabled())
				{
					$spacePosition = strpos($values['label'],' ');
					if($spacePosition === false)
					{
						$spacePosition = strlen($values['label']);
					}				
					$link = Piwik_Url::getCurrentScriptName() . 
							Piwik_Url::getCurrentQueryStringWithParametersModified( array(
								'date' => substr($values['label'],0,$spacePosition),
								'module' => 'CoreHome',
								'action' => 'index',
								'viewDataTable' => null// we reset the viewDataTable parameter (useless in the link)
						));
					// add the dot on the chart and link it
					$line[$j]->add_link($dotValue, $link);
				}
				else
				{
					$line[$j]->add($dotValue);
				}
			}
		}
		$this->data_sets = $line;		
		$this->set_y_max( $maxData );
		$this->set_x_labels( $xLabels );
	}
	
	private function isLinkEnabled() 
	{
		static $linkEnabled;
		if(!isset($linkEnabled)) 
		{
			$linkEnabled = !Piwik_Common::getRequestVar('disableLink', 0, 'int');
		}
		return $linkEnabled;
	}
}
