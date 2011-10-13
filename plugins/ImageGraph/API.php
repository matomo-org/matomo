<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version 0.3.3_a
 * 
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

/**
 * The ImageGraph.get API call lets you generate a beautiful static PNG Graph for any existing Piwik report.
 * This API should be used when you want to generate a static PNG image (line plot, pie chart, vertical bar chart).
 * 
 * A few notes about some of the parameters available:
 * - $graphType defines the type of graph plotted, accepted values are: 'evolution', 'verticalBar', 'pie'
 * - $colors accepts a comma delimited list of colors that will overwrite the default Piwik colors 
 * - You can also customize the width, height, font size, metric being plotted (in case the API function specified returns several metrics).
 * 
 * @package Piwik_ImageGraph
 */ 
class Piwik_ImageGraph_API
{
	static private $instance = null;
	
	const GRAPH_TYPE_BASIC_LINE		= "evolution";
	const GRAPH_TYPE_BASIC_BAR		= "verticalBar";
	const GRAPH_TYPE_3D_PIE			= "3dPie";
	const GRAPH_TYPE_BASIC_PIE		= "pie";
	
	const GRAPH_OUTPUT_INLINE		= 0;
	const GRAPH_OUTPUT_FILE			= 1;
	
	private $GRAPH_COLOR_PIE_0			= "3C5A69";
	private $GRAPH_COLOR_PIE_1			= "679BB5";
	private $GRAPH_COLOR_PIE_2			= "695A3C";
	private $GRAPH_COLOR_PIE_3			= "B58E67";
	private $GRAPH_COLOR_PIE_4			= "8AA68A";
	private $GRAPH_COLOR_PIE_5			= "A4D2A6";
	private $GRAPH_COLOR_PIE_6			= "EEEEEE";
	private $GRAPH_COLOR_BAR_0			= "3B5AA9";
	private $GRAPH_COLOR_BAR_1			= "063E7E";
	private $GRAPH_COLOR_LINE			= "063E7E";
	const GRAPH_COLOR_PIE_0			= "3C5A69";
	const GRAPH_COLOR_PIE_1			= "679BB5";
	const GRAPH_COLOR_PIE_2			= "695A3C";
	const GRAPH_COLOR_PIE_3			= "B58E67";
	const GRAPH_COLOR_PIE_4			= "8AA68A";
	const GRAPH_COLOR_PIE_5			= "A4D2A6";
	const GRAPH_COLOR_PIE_6			= "EEEEEE";
	const GRAPH_COLOR_BAR_0			= "3B5AA9";
	const GRAPH_COLOR_BAR_1			= "063E7E";
	const GRAPH_COLOR_LINE			= "063E7E";
	
	const GRAPH_FONT_SIZE			= 9;
	const GRAPH_WIDTH				= 1044;
	const GRAPH_HEIGHT				= 290;

	/**
	 * @return Piwik_ImageGraph_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	public function get(	$idSite, $period, $date,
							$apiModule, $apiAction,
							$graphType = false, $outputType = false, $column = false, $showMetricTitle = true,
							$width = false, $height = false, $fontSize = false, $aliasedGraph = true, $colors = false
	)
	{
		// Health check - should we also test for GD2 only?
		$extensions = @get_loaded_extensions();
		if (!in_array('gd', $extensions))
		{
			throw new Exception("Error: To create graphs in Piwik, please enable GD php extension in php.ini, and restart your web server.");
		}
		
		// Parameters init
		if(empty($width))
		{
			$width = self::GRAPH_WIDTH;
		}
		if(empty($height))
		{
			$height = self::GRAPH_HEIGHT;
		}
		if(empty($fontSize))
		{
			$fontSize = self::GRAPH_FONT_SIZE;
		}
		if(empty($graphType))
		{
			$graphType = self::GRAPH_TYPE_BASIC_LINE;
		}
		if(empty($outputType))
		{
			$outputType = self::GRAPH_OUTPUT_INLINE;
		}
		$colors = explode(",", $colors);
		if(!is_array($colors))
		{
			$colors = array();
		}
		if(empty($showMetricTitle))
		{
			$showMetricTitle = false;
		}
		if(empty($aliasedGraph))
		{
			$aliasedGraph = false;
		}
		
		try
		{
			Piwik::checkUserHasViewAccess($idSite);
			
			//Set default colors if needed
			$count = -1;
			$needCount = 0;
			$constNameBase = "";
			switch($graphType)
			{
				case self::GRAPH_TYPE_3D_PIE:
				case self::GRAPH_TYPE_BASIC_PIE:
					$needCount = 7;
					$constNameBase = "GRAPH_COLOR_PIE_";
					break;
				case self::GRAPH_TYPE_BASIC_BAR:
					$needCount = 2;
					$constNameBase = "GRAPH_COLOR_BAR_";
					break;
				case self::GRAPH_TYPE_BASIC_LINE:
				default:
					$needCount = 1;
					$constNameBase = "GRAPH_COLOR_LINE";
					break;
			}
			while(++$count < $needCount)
			{
				if(!isset($colors[$count]))
				{
					if($needCount == 1)
					{
						$colors[$count] = $this->{$constNameBase};
					}
					else
					{
						$colors[$count] = $this->{$constNameBase.$count};
					}
				}
			}
			
			//Fetch the metadata for given api-action
			$metadata = Piwik_API_API::getInstance()->getMetadata($idSite, $apiModule, $apiAction);
			
			//If the metadata doesn´t provide any information about the dimension,
			//the $abscissaColumn could only be the date-index
			if(empty($metadata[0]["dimension"]))
			{
				$abscissaColumn = "date";
				$abscissaVal = Piwik_Translate("Piwik_Date");
			}
			else
			{
				$abscissaColumn = "label";
				$abscissaVal = $metadata[0]["dimension"];
			}
			
			$availableColumns = array_merge(array($abscissaColumn => $abscissaVal),
											!empty($metadata[0]["metrics"]) ? $metadata[0]["metrics"] : array(),
											!empty($metadata[0]["processedMetrics"]) ? $metadata[0]["processedMetrics"] : array(),
											!empty($metadata[0]["metricsGoal"]) ? $metadata[0]["metricsGoal"] : array(),
											!empty($metadata[0]["processedMetricsGoal"]) ? $metadata[0]["processedMetricsGoal"] : array()
			);
		
			if(empty($column))
			{
				$column = 'nb_visits';
				if(empty($availableColumns[$column]))
				{
					$column = key($availableColumns);
				}
			}
			
			//If the desired $ordinateColumn is not present, we have to throw an exception
			if(empty($column) || empty($availableColumns[$column]))
			{
				throw new Exception(Piwik_Translate("ImageGraph_ColumnOrdinateMissing", $column));
			}
			$metricTitle = $showMetricTitle ? $availableColumns[$column] : false;
			
			//Save original GET to reset after processing. Important for API-in-API-call
			$origGET = $_GET;
			
	    	//If a pie should be drawn the report should always be sorted and truncated,
			if(	$graphType == self::GRAPH_TYPE_3D_PIE ||
				$graphType == self::GRAPH_TYPE_BASIC_PIE
			)
			{
				$_GET["filter_sort_column"] = $column;
				//if filter_truncate is less-equal than 5, we don´t have to set it
				if(empty($_GET["filter_truncate"]) || $_GET["filter_truncate"] > 5)
					$_GET["filter_truncate"] = 5;
			}
			//else if a bar- or line chart should be drawn and the report has a dimension,
			//we should truncate if the dimension is unlimited 
			//We mustn´t sort the report, because this brakes the readability of the reports with limited dimension.
			else if(($graphType == self::GRAPH_TYPE_BASIC_BAR ||
					$graphType == self::GRAPH_TYPE_BASIC_LINE) &&
					!empty($metadata[0]['dimension']) &&
					empty($metadata[0]['constantRowsCount'])
			)
			{
				$filterTruncateGET = Piwik_Common::getRequestVar('filter_truncate', false, 'int');
				if($filterTruncateGET <= 0)
				{
					//if filter_truncate is less-equal than 24, we don´t have to set it
					if(empty($_GET["filter_truncate"]) || $_GET["filter_truncate"] > 24)
						$_GET["filter_truncate"] = 24;
				}
			}
			
			//Fetch the report for given site, date, period and api-action
			$report = Piwik_API_API::getInstance()->getProcessedReport(	$idSite, $period, $date,
																		$apiModule, $apiAction
			);
			
			//Copy the required data into arrays
			$abscissaSerie = $ordinateSerie = array();
			$count = 0;
			//If the report has no dimension we have one or many reports each with only one row within the reportdata.
			if(	empty($metadata[0]['dimension']) &&
				($graphType == self::GRAPH_TYPE_BASIC_BAR ||
				$graphType == self::GRAPH_TYPE_BASIC_LINE)
			)
			{
				//We get one report with one row for any reports called with 'period=range',
				//or all reports called with 'period!=range' and a non-dimensional date like 'date=yesterday'
				if(get_class($report["reportData"]) == "Piwik_DataTable_Simple")
				{
					$row = $report["reportData"]->getRows();
					$row = $row[0]->getColumns();
					
					$this->setDataFromRowHelper($report, $column, $row, $count, $abscissaSerie, false, $ordinateSerie);
				}
				//We get many reports with one row if the request is called with 'period!=range' and a dimensional date like 'date=lastX'
				else
				{
					$rowData = array();
					$countTemp = 0;
					foreach($report["reportData"]->metadata as $dateVal)
					{
						$rowData[$countTemp++] = array('rowId' => $dateVal['period']->getLocalizedShortString());
					}
					$countTemp = 0;
					foreach($report["reportData"]->getArray() as $row)
					{
						$rowData[$countTemp++]['row'] = $row;
					}
					foreach($rowData as $rowD)
					{
						$row = $rowD['row']->getRows();
						if(!array_key_exists(0, $row))
						{
							continue;
						}
						$row = $row[0]->getColumns();
						
						$this->setDataFromRowHelper($report, $column, $row, $count, $abscissaSerie, array('rowId' => $rowD['rowId']), $ordinateSerie);
					}
				}
			}
			//Otherwise we have rows
			else if(!empty($metadata[0]['dimension']))
			{
				foreach($report["reportData"]->getRows() as $rowId => $row)
				{
					$row = $row->getColumns();
					
					$this->setDataFromRowHelper($report, $column, $row, $count, $abscissaSerie, array('abscissaColumn' => $abscissaColumn), $ordinateSerie);
				}
			}
			else
			{
				throw new Exception('Invalid $graphType for this API function.');
			}
			
			if($count == 0)
			{
				throw new Exception(Piwik_Translate("General_NoDataForGraph"));
			}
			
			//Reset GET to original values
			$_GET = $origGET;
			
			//Setup the graph
			$graph = new Piwik_ImageGraph_ImageGraphObject($width, $height, $fontSize);
			$graph->setData($abscissaSerie, $ordinateSerie, $availableColumns[$abscissaColumn], $availableColumns[$column], "", $metricTitle, $aliasedGraph);
			
			switch($graphType)
			{
				case self::GRAPH_TYPE_BASIC_PIE:
					$graph->printBasicPieGraph($colors[0], $colors[1], $colors[2], $colors[3], $colors[4], $colors[5], $colors[6]);
					break;
				case self::GRAPH_TYPE_3D_PIE:
					$graph->print3dPieGraph($colors[0], $colors[1], $colors[2], $colors[3], $colors[4], $colors[5], $colors[6]);
					break;
				case self::GRAPH_TYPE_BASIC_BAR:
					$graph->printBasicBarGraph($colors[0], $colors[1]);
					break;
				case self::GRAPH_TYPE_BASIC_LINE:
				default:
					$graph->printBasicLineGraph($colors[0]);
					break;
			}
			
		} catch (Exception $e) {
			$graph = new Piwik_ImageGraph_ImageGraphObject($width, $height, $fontSize);
			$graph->printException($e);
		}
			
		//Decide, whether the img is saved temporarily or displayed in the browser
		switch($outputType)
		{
			case self::GRAPH_OUTPUT_FILE:
				switch($graphType)
				{
					case self::GRAPH_TYPE_BASIC_PIE:
						$typeName = "BasicPie";
						break;
					case self::GRAPH_TYPE_3D_PIE:
						$typeName = "3DPie";
						break;
					case self::GRAPH_TYPE_BASIC_BAR:
						$typeName = "BasicBar";
						break;
					case self::GRAPH_TYPE_BASIC_LINE:
					default:
						$typeName = "BasicLine";
						break;
				}
				$fileName = $typeName."_".$apiModule."_".$apiAction." $date $idSite.png";
				$fileName = str_replace(array(" ","/"), "_", $fileName);
				if(!Piwik_Common::isValidFilename($fileName))
				{
					throw new Exception("Error: Image graph filename '$fileName' is not valid."); 
				}
				$path = PIWIK_INCLUDE_PATH."/tmp/".$fileName;
				$graph->Render($path);
				return $path;
			case self::GRAPH_OUTPUT_INLINE:
			default:
				$graph->Stroke();
		}
	}
	
	private function setDataFromRowHelper($report, $column, $rowData, &$count, &$abscissaSerie, $abscissaOptions, &$ordinateSerie)
	{
		if(!empty($abscissaOptions['rowId']))
		{
			$afterDay = stripos($abscissaOptions['rowId'], " ");
			if(is_numeric($afterDay) && $afterDay > 0)
			{
				$afterDay = substr($abscissaOptions['rowId'], 0, $afterDay);
			}
			$abscissaSerie[$count] = Piwik_Common::unsanitizeInputValue($abscissaOptions['rowId']);
		}
		else if(!empty($abscissaOptions['abscissaColumn']))
		{
			$abscissaSerie[$count] = Piwik_Common::unsanitizeInputValue($rowData[$abscissaOptions['abscissaColumn']]);
		}
		else
		{
			$abscissaSerie[$count] = $report["prettyDate"];
		}
		
		$ordinateSerie[$count] = @str_replace("%", "", @str_replace(",", ".", $rowData[$column]));
		if(@sscanf($ordinateSerie[$count], "%02d:%02d:%02d", $hour, $min, $sec) === 3)
		{
			$ordinateSerie[$count] = ((($hour*60) + $min) * 60) + $sec;
		}
		$count++;
	}
	
}