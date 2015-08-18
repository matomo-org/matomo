<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph;

use Exception;
use Piwik\API\Request;
use Piwik\Archive\DataTableFactory;
use Piwik\Common;
use Piwik\Filesystem;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\ImageGraph\StaticGraph;
use Piwik\SettingsServer;
use Piwik\Translate;

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
 * @method static \Piwik\Plugins\ImageGraph\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    const FILENAME_KEY = 'filename';
    const TRUNCATE_KEY = 'truncate';
    const WIDTH_KEY = 'width';
    const HEIGHT_KEY = 'height';
    const MAX_WIDTH = 2048;
    const MAX_HEIGHT = 2048;

    private static $DEFAULT_PARAMETERS = array(
        StaticGraph::GRAPH_TYPE_BASIC_LINE     => array(
            self::FILENAME_KEY => 'BasicLine',
            self::TRUNCATE_KEY => 6,
            self::WIDTH_KEY    => 1044,
            self::HEIGHT_KEY   => 290,
        ),
        StaticGraph::GRAPH_TYPE_VERTICAL_BAR   => array(
            self::FILENAME_KEY => 'BasicBar',
            self::TRUNCATE_KEY => 6,
            self::WIDTH_KEY    => 1044,
            self::HEIGHT_KEY   => 290,
        ),
        StaticGraph::GRAPH_TYPE_HORIZONTAL_BAR => array(
            self::FILENAME_KEY => 'HorizontalBar',
            self::TRUNCATE_KEY => null, // horizontal bar graphs are dynamically truncated
            self::WIDTH_KEY    => 800,
            self::HEIGHT_KEY   => 290,
        ),
        StaticGraph::GRAPH_TYPE_3D_PIE         => array(
            self::FILENAME_KEY => '3DPie',
            self::TRUNCATE_KEY => 5,
            self::WIDTH_KEY    => 1044,
            self::HEIGHT_KEY   => 290,
        ),
        StaticGraph::GRAPH_TYPE_BASIC_PIE      => array(
            self::FILENAME_KEY => 'BasicPie',
            self::TRUNCATE_KEY => 5,
            self::WIDTH_KEY    => 1044,
            self::HEIGHT_KEY   => 290,
        ),
    );

    private static $DEFAULT_GRAPH_TYPE_OVERRIDE = array(
        'Referrers_getReferrerType' => array(
            false // override if !$isMultiplePeriod
            => StaticGraph::GRAPH_TYPE_HORIZONTAL_BAR,
        ),
    );

    const GRAPH_OUTPUT_INLINE = 0;
    const GRAPH_OUTPUT_FILE = 1;
    const GRAPH_OUTPUT_PHP = 2;

    const DEFAULT_ORDINATE_METRIC = 'nb_visits';
    const FONT_DIR = '/plugins/ImageGraph/fonts/';
    const DEFAULT_FONT = 'tahoma.ttf';
    const UNICODE_FONT = 'unifont.ttf';
    const DEFAULT_FONT_SIZE = 9;
    const DEFAULT_LEGEND_FONT_SIZE_OFFSET = 2;
    const DEFAULT_TEXT_COLOR = '222222';
    const DEFAULT_BACKGROUND_COLOR = 'FFFFFF';
    const DEFAULT_GRID_COLOR = 'CCCCCC';

    // number of row evolutions to plot when no labels are specified, can be overridden using &filter_limit
    const DEFAULT_NB_ROW_EVOLUTIONS = 5;
    const MAX_NB_ROW_LABELS = 10;

    public function get(
        $idSite,
        $period,
        $date,
        $apiModule,
        $apiAction,
        $graphType = false,
        $outputType = API::GRAPH_OUTPUT_INLINE,
        $columns = false,
        $labels = false,
        $showLegend = true,
        $width = false,
        $height = false,
        $fontSize = API::DEFAULT_FONT_SIZE,
        $legendFontSize = false,
        $aliasedGraph = true,
        $idGoal = false,
        $colors = false,
        $textColor = API::DEFAULT_TEXT_COLOR,
        $backgroundColor = API::DEFAULT_BACKGROUND_COLOR,
        $gridColor = API::DEFAULT_GRID_COLOR,
        $idSubtable = false,
        $legendAppendMetric = true,
        $segment = false
    )
    {
        Piwik::checkUserHasViewAccess($idSite);

        // Health check - should we also test for GD2 only?
        if (!SettingsServer::isGdExtensionEnabled()) {
            throw new Exception('Error: To create graphs in Piwik, please enable GD php extension (with Freetype support) in php.ini,
            and restart your web server.');
        }

        $useUnicodeFont = array(
            'am', 'ar', 'el', 'fa', 'fi', 'he', 'ja', 'ka', 'ko', 'te', 'th', 'zh-cn', 'zh-tw',
        );
        $languageLoaded = Translate::getLanguageLoaded();
        $font = self::getFontPath(self::DEFAULT_FONT);
        if (in_array($languageLoaded, $useUnicodeFont)) {
            $unicodeFontPath = self::getFontPath(self::UNICODE_FONT);
            $font = file_exists($unicodeFontPath) ? $unicodeFontPath : $font;
        }

        // save original GET to reset after processing. Important for API-in-API-call
        $savedGET = $_GET;

        try {
            $apiParameters = array();
            if (!empty($idGoal)) {
                $apiParameters = array('idGoal' => $idGoal);
            }
            // Fetch the metadata for given api-action
            $parameters = array(
                'idSite' => $idSite,
                'apiModule' => $apiModule,
                'apiAction' => $apiAction,
                'apiParameters' => $apiParameters,
                'language' => $languageLoaded,
                'period' => $period,
                'date' => $date,
                'hideMetricsDoc' => false,
                'showSubtableReports' => true
            );

            $metadata = Request::processRequest('API.getMetadata', $parameters);
            if (!$metadata) {
                throw new Exception('Invalid API Module and/or API Action');
            }

            $metadata = $metadata[0];
            $reportHasDimension = !empty($metadata['dimension']);
            $constantRowsCount = !empty($metadata['constantRowsCount']);

            $isMultiplePeriod = Period::isMultiplePeriod($date, $period);
            if (!$reportHasDimension && !$isMultiplePeriod) {
                throw new Exception('The graph cannot be drawn for this combination of \'date\' and \'period\' parameters.');
            }

            if (empty($legendFontSize)) {
                $legendFontSize = (int)$fontSize + self::DEFAULT_LEGEND_FONT_SIZE_OFFSET;
            }

            if (empty($graphType)) {
                if ($isMultiplePeriod) {
                    $graphType = StaticGraph::GRAPH_TYPE_BASIC_LINE;
                } else {
                    if ($constantRowsCount) {
                        $graphType = StaticGraph::GRAPH_TYPE_VERTICAL_BAR;
                    } else {
                        $graphType = StaticGraph::GRAPH_TYPE_HORIZONTAL_BAR;
                    }
                }

                $reportUniqueId = $metadata['uniqueId'];
                if (isset(self::$DEFAULT_GRAPH_TYPE_OVERRIDE[$reportUniqueId][$isMultiplePeriod])) {
                    $graphType = self::$DEFAULT_GRAPH_TYPE_OVERRIDE[$reportUniqueId][$isMultiplePeriod];
                }
            } else {
                $availableGraphTypes = StaticGraph::getAvailableStaticGraphTypes();
                if (!in_array($graphType, $availableGraphTypes)) {
                    throw new Exception(
                        Piwik::translate(
                            'General_ExceptionInvalidStaticGraphType',
                            array($graphType, implode(', ', $availableGraphTypes))
                        )
                    );
                }
            }

            $width = (int)$width;
            $height = (int)$height;
            if (empty($width)) {
                $width = self::$DEFAULT_PARAMETERS[$graphType][self::WIDTH_KEY];
            }
            if (empty($height)) {
                $height = self::$DEFAULT_PARAMETERS[$graphType][self::HEIGHT_KEY];
            }

            // Cap width and height to a safe amount
            $width = min($width, self::MAX_WIDTH);
            $height = min($height, self::MAX_HEIGHT);

            $reportColumns = array_merge(
                !empty($metadata['metrics']) ? $metadata['metrics'] : array(),
                !empty($metadata['processedMetrics']) ? $metadata['processedMetrics'] : array(),
                !empty($metadata['metricsGoal']) ? $metadata['metricsGoal'] : array(),
                !empty($metadata['processedMetricsGoal']) ? $metadata['processedMetricsGoal'] : array()
            );

            $ordinateColumns = array();
            if (empty($columns)) {
                $ordinateColumns[] =
                    empty($reportColumns[self::DEFAULT_ORDINATE_METRIC]) ? key($metadata['metrics']) : self::DEFAULT_ORDINATE_METRIC;
            } else {
                $ordinateColumns = explode(',', $columns);
                foreach ($ordinateColumns as $column) {
                    if (empty($reportColumns[$column])) {
                        throw new Exception(
                            Piwik::translate(
                                'ImageGraph_ColumnOrdinateMissing',
                                array($column, implode(',', array_keys($reportColumns)))
                            )
                        );
                    }
                }
            }

            $ordinateLabels = array();
            foreach ($ordinateColumns as $column) {
                $ordinateLabels[$column] = $reportColumns[$column];
            }

            // sort and truncate filters
            $defaultFilterTruncate = self::$DEFAULT_PARAMETERS[$graphType][self::TRUNCATE_KEY];
            switch ($graphType) {
                case StaticGraph::GRAPH_TYPE_3D_PIE:
                case StaticGraph::GRAPH_TYPE_BASIC_PIE:

                    if (count($ordinateColumns) > 1) {
                        // pChart doesn't support multiple series on pie charts
                        throw new Exception("Pie charts do not currently support multiple series");
                    }

                    $_GET['filter_sort_column'] = reset($ordinateColumns);
                    $this->setFilterTruncate($defaultFilterTruncate);
                    break;

                case StaticGraph::GRAPH_TYPE_VERTICAL_BAR:
                case StaticGraph::GRAPH_TYPE_BASIC_LINE:

                    if (!$isMultiplePeriod && !$constantRowsCount) {
                        $this->setFilterTruncate($defaultFilterTruncate);
                    }
                    break;
            }

            $ordinateLogos = array();

            // row evolutions
            if ($isMultiplePeriod && $reportHasDimension) {
                $plottedMetric = reset($ordinateColumns);

                // when no labels are specified, getRowEvolution returns the top N=filter_limit row evolutions
                // rows are sorted using filter_sort_column (see DataTableGenericFilter for more info)
                if (!$labels) {
                    $savedFilterSortColumnValue = Common::getRequestVar('filter_sort_column', '');
                    $_GET['filter_sort_column'] = $plottedMetric;

                    $savedFilterLimitValue = Common::getRequestVar('filter_limit', -1, 'int');
                    if ($savedFilterLimitValue == -1 || $savedFilterLimitValue > self::MAX_NB_ROW_LABELS) {
                        $_GET['filter_limit'] = self::DEFAULT_NB_ROW_EVOLUTIONS;
                    }
                }

                $parameters = array(
                    'idSite' => $idSite,
                    'period' => $period,
                    'date' => $date,
                    'apiModule' => $apiModule,
                    'apiAction' => $apiAction,
                    'label' => $labels,
                    'segment' => $segment,
                    'column' => $plottedMetric,
                    'language' => $languageLoaded,
                    'idGoal' => $idGoal,
                    'legendAppendMetric' => $legendAppendMetric,
                    'labelUseAbsoluteUrl' => false
                );
                $processedReport = Request::processRequest('API.getRowEvolution', $parameters);

                //@review this test will need to be updated after evaluating the @review comment in API/API.php
                if (!$processedReport) {
                    throw new Exception(Piwik::translate('General_NoDataForGraph'));
                }

                // restoring generic filter parameters
                if (!$labels) {
                    $_GET['filter_sort_column'] = $savedFilterSortColumnValue;
                    if ($savedFilterLimitValue != -1) {
                        $_GET['filter_limit'] = $savedFilterLimitValue;
                    }
                }

                // retrieve metric names & labels
                $metrics = $processedReport['metadata']['metrics'];
                $ordinateLabels = array();

                // getRowEvolution returned more than one label
                if (!array_key_exists($plottedMetric, $metrics)) {
                    $ordinateColumns = array();
                    $i = 0;
                    foreach ($metrics as $metric => $info) {
                        $ordinateColumn = $plottedMetric . '_' . $i++;
                        $ordinateColumns[] = $metric;
                        $ordinateLabels[$ordinateColumn] = $info['name'];

                        if (isset($info['logo'])) {
                            $ordinateLogo = $info['logo'];

                            // @review pChart does not support gifs in graph legends, would it be possible to convert all plugin pictures (cookie.gif, flash.gif, ..) to png files?
                            if (!strstr($ordinateLogo, '.gif')) {
                                $absoluteLogoPath = self::getAbsoluteLogoPath($ordinateLogo);
                                if (file_exists($absoluteLogoPath)) {
                                    $ordinateLogos[$ordinateColumn] = $absoluteLogoPath;
                                }
                            }
                        }
                    }
                } else {
                    $ordinateLabels[$plottedMetric] = $processedReport['label'] . ' (' . $metrics[$plottedMetric]['name'] . ')';
                }
            } else {
                $parameters = array(
                    'idSite' => $idSite,
                    'period' => $period,
                    'date' => $date,
                    'apiModule' => $apiModule,
                    'apiAction' => $apiAction,
                    'segment' => $segment,
                    'apiParameters' => false,
                    'idGoal' => $idGoal,
                    'language' => $languageLoaded,
                    'showTimer' => true,
                    'hideMetricsDoc' => false,
                    'idSubtable' => $idSubtable,
                    'showRawMetrics' => false
                );
                $processedReport = Request::processRequest('API.getProcessedReport', $parameters);
            }

            // prepare abscissa and ordinate series
            $abscissaSeries = array();
            $abscissaLogos = array();
            $ordinateSeries = array();
            /** @var \Piwik\DataTable\Simple|\Piwik\DataTable\Map $reportData */
            $reportData = $processedReport['reportData'];
            $hasData = false;
            $hasNonZeroValue = false;

            if (!$isMultiplePeriod) {
                $reportMetadata = $processedReport['reportMetadata']->getRows();

                $i = 0;
                // $reportData instanceof DataTable
                foreach ($reportData->getRows() as $row) // Row[]
                {
                    // $row instanceof Row
                    $rowData = $row->getColumns(); // Associative Array
                    $abscissaSeries[] = Common::unsanitizeInputValue($rowData['label']);

                    foreach ($ordinateColumns as $column) {
                        $parsedOrdinateValue = $this->parseOrdinateValue($rowData[$column]);
                        $hasData = true;

                        if ($parsedOrdinateValue != 0) {
                            $hasNonZeroValue = true;
                        }
                        $ordinateSeries[$column][] = $parsedOrdinateValue;
                    }

                    if (isset($reportMetadata[$i])) {
                        $rowMetadata = $reportMetadata[$i]->getColumns();
                        if (isset($rowMetadata['logo'])) {
                            $absoluteLogoPath = self::getAbsoluteLogoPath($rowMetadata['logo']);
                            if (file_exists($absoluteLogoPath)) {
                                $abscissaLogos[$i] = $absoluteLogoPath;
                            }
                        }
                    }
                    $i++;
                }
            } else // if the report has no dimension we have multiple reports each with only one row within the reportData
            {
                // $periodsData instanceof Simple[]
                $periodsData = array_values($reportData->getDataTables());
                $periodsCount = count($periodsData);

                for ($i = 0; $i < $periodsCount; $i++) {
                    // $periodsData[$i] instanceof Simple
                    // $rows instanceof Row[]
                    if (empty($periodsData[$i])) {
                        continue;
                    }
                    $rows = $periodsData[$i]->getRows();

                    if (array_key_exists(0, $rows)) {
                        $rowData = $rows[0]->getColumns(); // associative Array

                        foreach ($ordinateColumns as $column) {
                            if(!isset($rowData[$column])) {
                                continue;
                            }
                            $ordinateValue = $rowData[$column];
                            $parsedOrdinateValue = $this->parseOrdinateValue($ordinateValue);

                            $hasData = true;

                            if (!empty($parsedOrdinateValue)) {
                                $hasNonZeroValue = true;
                            }

                            $ordinateSeries[$column][] = $parsedOrdinateValue;
                        }
                    } else {
                        foreach ($ordinateColumns as $column) {
                            $ordinateSeries[$column][] = 0;
                        }
                    }

                    $rowId = $periodsData[$i]->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLocalizedShortString();
                    $abscissaSeries[] = Common::unsanitizeInputValue($rowId);
                }
            }

            if (!$hasData || !$hasNonZeroValue) {
                throw new Exception(Piwik::translate('General_NoDataForGraph'));
            }

            //Setup the graph
            $graph = StaticGraph::factory($graphType);
            $graph->setWidth($width);
            $graph->setHeight($height);
            $graph->setFont($font);
            $graph->setFontSize($fontSize);
            $graph->setLegendFontSize($legendFontSize);
            $graph->setOrdinateLabels($ordinateLabels);
            $graph->setShowLegend($showLegend);
            $graph->setAliasedGraph($aliasedGraph);
            $graph->setAbscissaSeries($abscissaSeries);
            $graph->setAbscissaLogos($abscissaLogos);
            $graph->setOrdinateSeries($ordinateSeries);
            $graph->setOrdinateLogos($ordinateLogos);
            $graph->setColors(!empty($colors) ? explode(',', $colors) : array());
            $graph->setTextColor($textColor);
            $graph->setBackgroundColor($backgroundColor);
            $graph->setGridColor($gridColor);

            // when requested period is day, x-axis unit is time and all date labels can not be displayed
            // within requested width, force labels to be skipped every 6 days to delimit weeks
            if ($period == 'day' && $isMultiplePeriod) {
                $graph->setForceSkippedLabels(6);
            }

            // render graph
            $graph->renderGraph();
        } catch (\Exception $e) {

            $graph = new \Piwik\Plugins\ImageGraph\StaticGraph\Exception();
            $graph->setWidth($width);
            $graph->setHeight($height);
            $graph->setFont($font);
            $graph->setFontSize($fontSize);
            $graph->setBackgroundColor($backgroundColor);
            $graph->setTextColor($textColor);
            $graph->setException($e);
            $graph->renderGraph();
        }

        // restoring get parameters
        $_GET = $savedGET;

        switch ($outputType) {
            case self::GRAPH_OUTPUT_FILE:
                if ($idGoal != '') {
                    $idGoal = '_' . $idGoal;
                }
                $fileName = self::$DEFAULT_PARAMETERS[$graphType][self::FILENAME_KEY] . '_' . $apiModule . '_' . $apiAction . $idGoal . ' ' . str_replace(',', '-', $date) . ' ' . $idSite . '.png';
                $fileName = str_replace(array(' ', '/'), '_', $fileName);

                if (!Filesystem::isValidFilename($fileName)) {
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
        $_GET['filter_truncate'] = Common::getRequestVar('filter_truncate', $default, 'int');
    }

    private static function parseOrdinateValue($ordinateValue)
    {
        $ordinateValue = @str_replace(',', '.', $ordinateValue);

        // convert hh:mm:ss formatted time values to number of seconds
        if (preg_match('/([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/', $ordinateValue, $matches)) {
            $hour = $matches[1];
            $min = $matches[2];
            $sec = $matches[3];

            $ordinateValue = ($hour * 3600) + ($min * 60) + $sec;
        }

        // OK, only numbers from here please (strip out currency sign)
        $ordinateValue = preg_replace('/[^0-9.]/', '', $ordinateValue);
        return $ordinateValue;
    }

    private static function getFontPath($font)
    {
        return PIWIK_INCLUDE_PATH . self::FONT_DIR . $font;
    }

    protected static function getAbsoluteLogoPath($relativeLogoPath)
    {
        return PIWIK_INCLUDE_PATH . '/' . $relativeLogoPath;
    }
}
