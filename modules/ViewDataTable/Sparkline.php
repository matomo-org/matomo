<?php
class Piwik_ViewDataTable_Sparkline extends Piwik_ViewDataTable
{
	
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod )
	{
		parent::init($currentControllerName, 
						$currentControllerAction, 
						$moduleNameAndMethod );
	}
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
	
		// we load the data with the filters applied
		$this->loadDataTableFromAPI();
		
//		echo $this->dataTable; exit;
		$this->dataAvailable = $this->dataTable->getRowsCount() != 0;
		
		if(!$this->dataAvailable)
		{
			throw new Exception( "No data for this graph" );
		}
		else
		{
			$data = $this->generateDataFromDataTableArray($this->dataTable);
			
			$graph = new Piwik_Sparkline_Graph;
			$graph->setData($data);
			$graph->main();
//			var_dump($data);exit;
			$this->view = $graph;
		}
	}
	
}

require_once 'sparkline/lib/Sparkline_Line.php';

class Piwik_Sparkline_Graph
{
	function setData($data)
	{
		$this->data = $data;
	}
	
	function main()
	{
		$data = $this->data;
		$sparkline = new Sparkline_Line();
		
//		$sparkline->SetColorHtml('lineColor', '000000');
		$sparkline->SetColor('lineColor', 22,44,74); // dark blue
//		$sparkline->SetColor('lineColor', 0,119,204);
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
//		echo imagefontwidth(FONT_2);exit;
		// set y-bound, min and max extent lines
		//
		$sparkline->SetYMin(0);
//		$sparkline->SetYMax($max);
		$sparkline->SetPadding(2); // setpadding is additive
		$sparkline->SetPadding(0,//13,//font height 
					3,//4 * (strlen("$last[1]")), 
					0, //imagefontheight(FONT_2), 
					0);
		$font = FONT_2;
		$sparkline->SetFeaturePoint($min[0]-1,$min[1],'red', 5);//, $min[1], TEXT_TOP,$font);
		$sparkline->SetFeaturePoint($max[0]-1,$max[1],  'green', 5);//, $max[1], TEXT_TOP,$font);
		$sparkline->SetFeaturePoint($last[0]-1, $last[1], 'blue',5);//, " $last[1]", TEXT_RIGHT,$font);
		
		$sparkline->SetLineSize(3); // for renderresampled, linesize is on virtual image
		$sparkline->RenderResampled(100, 20, 'lineColor');
		
		$this->sparkline = $sparkline;
	}
	
	function render()
	{
		$this->sparkline->Output();
	}
}

?>