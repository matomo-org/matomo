<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

/**
 * The ImageGraph.get API call lets you generate beautiful static PNG Graphs for any existing Piwik report.
 * Supported graph types are: line plot, 2D/3D pie chart and vertical bar chart.
 * 
 * A few notes about some of the parameters available:<br/>
 * - $graphType defines the type of graph plotted, accepted values are: 'evolution', 'verticalBar', 'pie' and '3dPie'<br/>
 * - $colors accepts a comma delimited list of colors that will overwrite the default Piwik colors <br/>
 * - you can also customize the width, height, font size, metric being plotted (in case the data contains multiple columns/metrics).
 * 
 * See also <a href='http://piwik.org/docs/analytics-api/metadata/#toc-static-image-graphs'>How to embed static Image Graphs?</a> for more information.
 * 
 * @package Piwik_ImageGraph
 */ 
class Piwik_ImageGraph_API
{
	const FILENAME_KEY = 'filename';
	const TRUNCATE_KEY = 'truncate';
	const WIDTH_KEY = 'width';
	const HEIGHT_KEY = 'height';
	const MAX_WIDTH = 1024;
	const MAX_HEIGHT = 1024; 

	static private $DEFAULT_PARAMETERS = array(
		Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_BASIC_LINE => array(
			self::FILENAME_KEY => 'BasicLine',
			self::TRUNCATE_KEY => 6,
			self::WIDTH_KEY => 1044,
			self::HEIGHT_KEY => 290,
		),
		Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_VERTICAL_BAR => array(
			self::FILENAME_KEY => 'BasicBar',
			self::TRUNCATE_KEY => 6,
			self::WIDTH_KEY => 1044,
			self::HEIGHT_KEY => 290,
		),
		Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_HORIZONTAL_BAR => array(
			self::FILENAME_KEY => 'HorizontalBar',
			self::TRUNCATE_KEY => null, // horizontal bar graphs are dynamically truncated
			self::WIDTH_KEY => 800,
			self::HEIGHT_KEY => 290,
		),
		Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_3D_PIE => array(
			self::FILENAME_KEY => '3DPie',
			self::TRUNCATE_KEY => 5,
			self::WIDTH_KEY => 1044,
			self::HEIGHT_KEY => 290,
		),
		Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_BASIC_PIE => array(
			self::FILENAME_KEY => 'BasicPie',
			self::TRUNCATE_KEY => 5,
			self::WIDTH_KEY => 1044,
			self::HEIGHT_KEY => 290,
		),
	);

	static private $DEFAULT_GRAPH_TYPE_OVERRIDE = array(
		'UserSettings_getPlugin' => Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_HORIZONTAL_BAR,
	);

	const GRAPH_OUTPUT_INLINE = 0;
	const GRAPH_OUTPUT_FILE = 1;
	const GRAPH_OUTPUT_PHP = 2;

	const DEFAULT_ORDINATE_METRIC = 'nb_visits';
	const FONT_DIR = '/plugins/ImageGraph/fonts/';
	const DEFAULT_FONT = 'tahoma.ttf';
	const UNICODE_FONT = 'unifont.ttf';
	const DEFAULT_FONT_SIZE = 9;

	static private $instance = null;

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
	
	public function get($idSite, $period, $date, $apiModule, $apiAction, $graphType = false,
						$outputType = Piwik_ImageGraph_API::GRAPH_OUTPUT_INLINE, $column = false, $showMetricTitle = true,
						$width = false, $height = false, $fontSize = Piwik_ImageGraph_API::DEFAULT_FONT_SIZE, $aliasedGraph = true,
						$idGoal = false, $colors = false)
	{
		Piwik::checkUserHasViewAccess($idSite);

		// Health check - should we also test for GD2 only?
		if(!Piwik::isGdExtensionEnabled())
		{
			throw new Exception('Error: To create graphs in Piwik, please enable GD php extension (with Freetype support) in php.ini, and restart your web server.');
		}

		$useUnicodeFont = array(
			'am', 'ar', 'el', 'fa' , 'fi', 'he', 'ja', 'ka', 'ko', 'te', 'th', 'zh-cn', 'zh-tw', 
		);
		$languageLoaded = Piwik_Translate::getInstance()->getLanguageLoaded();
		$font = self::getFontPath(self::DEFAULT_FONT);
		if(in_array($languageLoaded, $useUnicodeFont))
		{
			$unicodeFontPath = self::getFontPath(self::UNICODE_FONT);
			$font = file_exists($unicodeFontPath) ? $unicodeFontPath : $font;
		}
		
		// save original GET to reset after processing. Important for API-in-API-call
		$savedGET = $_GET;

		try
		{
			//Fetch the metadata for given api-action
			$metadata = Piwik_API_API::getInstance()->getMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $languageLoaded, $period, $date);
			if(!$metadata)
			{
				throw new Exception('Invalid API Module and/or API Action');
			}

			$metadata = $metadata[0];
			$reportHasDimension = !empty($metadata['dimension']);
			$constantRowsCount = !empty($metadata['constantRowsCount']);

			$isMultiplePeriod = Piwik_Archive::isMultiplePeriod($date, $period);
			if(($reportHasDimension && $isMultiplePeriod) || (!$reportHasDimension && !$isMultiplePeriod))
			{
				throw new Exception('The graph cannot be drawn for this combination of \'date\' and \'period\' parameters.');
			}

			if(empty($graphType))
			{
				if($reportHasDimension)
				{
					if($constantRowsCount)
					{
						$graphType = Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_VERTICAL_BAR;
					}
					else
					{
						$graphType = Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_HORIZONTAL_BAR;
					}
				}
				else
				{
					$graphType = Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_BASIC_LINE;
				}

				$reportUniqueId = $metadata['uniqueId'];
				if(isset(self::$DEFAULT_GRAPH_TYPE_OVERRIDE[$reportUniqueId]))
				{
					$graphType = self::$DEFAULT_GRAPH_TYPE_OVERRIDE[$reportUniqueId];
				}
			}
			else
			{
				$availableGraphTypes = Piwik_ImageGraph_StaticGraph::getAvailableStaticGraphTypes();
				if (!in_array($graphType, $availableGraphTypes))
				{
					throw new Exception(
						Piwik_TranslateException(
							'General_ExceptionInvalidStaticGraphType',
							array($graphType, implode(', ', $availableGraphTypes))
						)
					);
				}
			}

			if(empty($width))
			{
				$width = self::$DEFAULT_PARAMETERS[$graphType][self::WIDTH_KEY];
			}
			if(empty($height))
			{
				$height = self::$DEFAULT_PARAMETERS[$graphType][self::HEIGHT_KEY];
			}
			// Cap width and height to a safe amount
			$width = min($width, self::MAX_WIDTH);
			$height = min($height, self::MAX_HEIGHT);

			if($reportHasDimension)
			{
				$abscissaColumn = 'label';
			}
			else
			{
				// if it's a dimension-less report, the abscissa column can only be the date-index
				$abscissaColumn = 'date';
			}
			
			$reportColumns = array_merge(
				!empty($metadata['metrics']) ? $metadata['metrics'] : array(),
				!empty($metadata['processedMetrics']) ? $metadata['processedMetrics'] : array(),
				!empty($metadata['metricsGoal']) ? $metadata['metricsGoal'] : array(),
				!empty($metadata['processedMetricsGoal']) ? $metadata['processedMetricsGoal'] : array()
			);

			$ordinateColumn = $column;
			if(empty($ordinateColumn))
			{
				$ordinateColumn = self::DEFAULT_ORDINATE_METRIC;

				// if default ordinate metric not available for this report
				if(empty($reportColumns[$ordinateColumn]))
				{
					// take the first metric returned in the metadata
					$ordinateColumn = key($metadata['metrics']);
				}
			}
			
			// if we still don't have an ordinate column or the one provided by the API caller is invalid
			if(empty($ordinateColumn) || empty($reportColumns[$ordinateColumn]))
			{
				throw new Exception(Piwik_Translate('ImageGraph_ColumnOrdinateMissing', $ordinateColumn));
			}

			$ordinateLabel = $reportColumns[$ordinateColumn];

			// sort and truncate filters
			$defaultFilterTruncate = self::$DEFAULT_PARAMETERS[$graphType][self::TRUNCATE_KEY];
			switch($graphType)
			{
				case Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_3D_PIE:
				case Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_BASIC_PIE:

					$_GET['filter_sort_column'] = $ordinateColumn;
					$this->setFilterTruncate($defaultFilterTruncate);
					break;

				case Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_VERTICAL_BAR:
				case Piwik_ImageGraph_StaticGraph::GRAPH_TYPE_BASIC_LINE:

					if($reportHasDimension && !$constantRowsCount)
					{
						$this->setFilterTruncate($defaultFilterTruncate);
					}
					break;
			}

			$processedReport = Piwik_API_API::getInstance()->getProcessedReport(
				$idSite,
				$period,
				$date,
				$apiModule,
				$apiAction,
				$segment = false,
				$apiParameters = false, 
				$idGoal, 
				$languageLoaded
			);
			// prepare abscissa and ordinate series
			$abscissaSerie = array();
			$ordinateSerie = array();
			$ordinateLogos = array();
			$reportData = $processedReport['reportData'];
			$hasData = false;
			$hasNonZeroValue = false;

			if($reportHasDimension)
			{
				$reportMetadata = $processedReport['reportMetadata']->getRows();

				$i = 0;
				// $reportData instanceof Piwik_DataTable
				foreach($reportData->getRows() as $row) // Piwik_DataTable_Row[]
				{
					// $row instanceof Piwik_DataTable_Row
					$rowData = $row->getColumns(); // Associative Array
					$abscissaSerie[] = Piwik_Common::unsanitizeInputValue($rowData[$abscissaColumn]);
					$parsedOrdinateValue = $this->parseOrdinateValue($rowData[$ordinateColumn]);

					$hasData = true;

					if($parsedOrdinateValue != 0)
					{
						$hasNonZeroValue = true;
					}

					$ordinateSerie[] = $parsedOrdinateValue;

					if(isset($reportMetadata[$i]))
					{
						$rowMetadata = $reportMetadata[$i]->getColumns();
						if(isset($rowMetadata['logo']))
						{
							$ordinateLogos[$i] = $rowMetadata['logo'];
						}
					}
					$i++;
				}
			}
			else // if the report has no dimension we have multiple reports each with only one row within the reportData
			{
				// $reportData instanceof Piwik_DataTable_Array
				$periodsMetadata = array_values($reportData->metadata);

				// $periodsData instanceof Piwik_DataTable_Simple[]
				$periodsData = array_values($reportData->getArray());
				$periodsCount = count($periodsMetadata);

				for ($i = 0 ; $i < $periodsCount ; $i++)
				{
					// $periodsData[$i] instanceof Piwik_DataTable_Simple
					// $rows instanceof Piwik_DataTable_Row[]
					$rows = $periodsData[$i]->getRows();

					if(array_key_exists(0, $rows))
					{
						$rowData = $rows[0]->getColumns(); // associative Array
						$ordinateValue = $rowData[$ordinateColumn];
						$parsedOrdinateValue = $this->parseOrdinateValue($ordinateValue);

						$hasData = true;

						if($parsedOrdinateValue != 0)
						{
							$hasNonZeroValue = true;
						}
					}
					else
					{
						$parsedOrdinateValue = 0;
					}

					$rowId = $periodsMetadata[$i]['period']->getLocalizedShortString();

					$abscissaSerie[] = Piwik_Common::unsanitizeInputValue($rowId);
					$ordinateSerie[] = $parsedOrdinateValue;
				}
			}

			if(!$hasData || !$hasNonZeroValue)
			{
				throw new Exception(Piwik_Translate('General_NoDataForGraph'));
			}
			
			//Setup the graph
			$graph = Piwik_ImageGraph_StaticGraph::factory($graphType);
			$graph->setWidth($width);
			$graph->setHeight($height);
			$graph->setFont($font);
			$graph->setFontSize($fontSize);
			$graph->setMetricTitle($ordinateLabel);
			$graph->setShowMetricTitle($showMetricTitle);
			$graph->setAliasedGraph($aliasedGraph);
			$graph->setAbscissaSerie($abscissaSerie);
			$graph->setOrdinateSerie($ordinateSerie);
			$graph->setOrdinateLogos($ordinateLogos);
			$graph->setColors(!empty($colors) ? explode(',', $colors) : array());

			// render graph
			$graph->renderGraph();
			
		} catch (Exception $e) {

			$graph = new Piwik_ImageGraph_StaticGraph_Exception();
			$graph->setWidth($width);
			$graph->setHeight($height);
			$graph->setFont($font);
			$graph->setFontSize($fontSize);
			$graph->setException($e);
			$graph->renderGraph();
		}

		// restoring get parameters
		$_GET = $savedGET;

		switch($outputType)
		{
			case self::GRAPH_OUTPUT_FILE:
				if($idGoal != '')
				{
					$idGoal = '_' . $idGoal;
				}
				$fileName = self::$DEFAULT_PARAMETERS[$graphType][self::FILENAME_KEY] . '_' . $apiModule . '_' . $apiAction . $idGoal . ' ' . str_replace(',', '-', $date) . ' ' . $idSite . '.png';
				$fileName = str_replace(array(' ','/'), '_', $fileName);

				if(!Piwik_Common::isValidFilename($fileName))
				{
					throw new Exception('Error: Image graph filename ' . $fileName . ' is not valid.');
				}

				return $graph->sendToDisk($fileName);

			case self::GRAPH_OUTPUT_PHP:
				return $graph->getRenderedImage();

			case self::GRAPH_OUTPUT_INLINE:
			default:
				$graph->sendToBrowser();
				exit;
		}
	}

	private function setFilterTruncate($default)
	{
		$_GET['filter_truncate'] = Piwik_Common::getRequestVar('filter_truncate', $default, 'int');
	}
	
	private static function parseOrdinateValue($ordinateValue)
	{
		$ordinateValue = @str_replace(',', '.', $ordinateValue);

		// convert hh:mm:ss formatted time values to number of seconds
		if(preg_match('/([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/', $ordinateValue, $matches))
		{
			$hour = $matches[1];
			$min = $matches[2];
			$sec = $matches[3];

			$ordinateValue = ($hour * 3600) + ($min * 60) + $sec;
		}

		return $ordinateValue;
	}

	private static function getFontPath($font)
	{
		return PIWIK_INCLUDE_PATH . self::FONT_DIR . $font;
	}
}