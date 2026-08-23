<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;
use Piwik\Plugins\CoreHome\Columns\Metrics\PercentOfReportTotal;
use Piwik\Plugins\ImageGraph\API;

/**
 * A Report Renderer produces user friendly renderings of any given Piwik report.
 * All new Renderers must be copied in ReportRenderer and added to the $availableReportRenderers.
 */
abstract class ReportRenderer extends BaseFactory
{
    public const DEFAULT_REPORT_FONT_FAMILY = 'dejavusans';
    public const REPORT_TEXT_COLOR = "13,13,13";
    public const REPORT_TITLE_TEXT_COLOR = "13,13,13";
    public const TABLE_HEADER_BG_COLOR = "255,255,255";
    public const TABLE_HEADER_TEXT_COLOR = "13,13,13";
    public const TABLE_HEADER_TEXT_TRANSFORM = "uppercase";
    public const TABLE_HEADER_TEXT_WEIGHT = "normal";
    public const TABLE_CELL_BORDER_COLOR = "217,217,217";
    public const TABLE_BG_COLOR = "242,242,242";

    public const HTML_FORMAT = 'html';
    public const PDF_FORMAT = 'pdf';
    public const CSV_FORMAT = 'csv';
    public const TSV_FORMAT = 'tsv';

    protected $idSite = 'all';

    protected $report;

    private static $availableReportRenderers = [
        self::PDF_FORMAT,
        self::HTML_FORMAT,
        self::CSV_FORMAT,
        self::TSV_FORMAT,
    ];

    /**
     * Sets the site id
     *
     * @param int $idSite
     */
    public function setIdSite($idSite)
    {
        $this->idSite = $idSite;
    }

    public function setReport($report)
    {
        $this->report = $report;
    }

    protected static function getClassNameFromClassId($rendererType)
    {
        return 'Piwik\ReportRenderer\\' . self::normalizeRendererType($rendererType);
    }

    protected static function getInvalidClassIdExceptionMessage($rendererType)
    {
        return Piwik::translate(
            'General_ExceptionInvalidReportRendererFormat',
            [self::normalizeRendererType($rendererType), implode(', ', self::$availableReportRenderers)]
        );
    }

    protected static function normalizeRendererType($rendererType)
    {
        return ucfirst(strtolower($rendererType));
    }

    /**
     * Initialize locale settings.
     * If not called, locale settings defaults to 'en'
     *
     * @param string $locale
     */
    abstract public function setLocale($locale);

    /**
     * Save rendering to disk
     *
     * @param string $filename without path & without format extension
     * @return string path of file
     */
    abstract public function sendToDisk($filename);

    /**
     * Send rendering to browser with a 'download file' prompt
     *
     * @param string $filename without path & without format extension
     */
    abstract public function sendToBrowserDownload($filename);

    /**
     * Output rendering to browser
     *
     * @param string $filename without path & without format extension
     */
    abstract public function sendToBrowserInline($filename);

    /**
     * Get rendered report
     *
     * @return string
     */
    abstract public function getRenderedReport();

    /**
     * Generate the first page.
     *
     * @param string $reportTitle
     * @param string $prettyDate formatted date
     * @param string $description
     * @param array $reportMetadata metadata for all reports
     * @param array $segment segment applied to all reports
     */
    abstract public function renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment);

    /**
     * Render the provided report.
     * Multiple calls to this method before calling getRenderedReport appends each report content.
     *
     * @param array $processedReport @see API::getProcessedReport()
     */
    abstract public function renderReport($processedReport);

    /**
     * Get report attachments, ex. graph images
     *
     * @param $report
     * @param $processedReports
     * @param $prettyDate
     * @return array
     */
    abstract public function getAttachments($report, $processedReports, $prettyDate);

    /**
     * Append $extension to $filename
     *
     * @static
     * @param  string $filename
     * @param  string $extension
     * @return string  filename with extension
     */
    protected static function makeFilenameWithExtension($filename, $extension)
    {
        // the filename can be used in HTTP headers, remove new lines to prevent HTTP header injection
        $filename = str_replace(["\n", "\t"], " ", $filename);

        return $filename . "." . $extension;
    }

    /**
     * Return $filename with temp directory and delete file
     *
     * @static
     * @param  $filename
     * @return string path of file in temp directory
     */
    protected static function getOutputPath($filename)
    {
        // Keep the generated file inside the assets directory: strip any directory components so the
        // filename can never point outside $baseAssetsDir when it is concatenated below.
        $filename = basename($filename);

        $baseAssetsDir = StaticContainer::get('path.tmp') . '/assets/';
        $outputFilename = $baseAssetsDir . $filename;

        if (!is_dir($baseAssetsDir)) {
            Filesystem::mkdir($baseAssetsDir);
        }

        @chmod($outputFilename, 0600);

        if (file_exists($outputFilename)) {
            @unlink($outputFilename);
        }

        return $outputFilename;
    }

    protected static function writeFile($filename, $extension, $content)
    {
        $filename = self::makeFilenameWithExtension($filename, $extension);
        $outputFilename = self::getOutputPath($filename);

        $bytesWritten = file_put_contents($outputFilename, $content);
        if ($bytesWritten === false) {
            throw new Exception("ReportRenderer: Could not write to file '" . $outputFilename . "'.");
        }

        return $outputFilename;
    }

    /**
     * Streaming a report writes response headers and body directly, so it is reserved for the
     * top-level request. A report generated as a nested API sub-request must be returned to the
     * calling request instead.
     *
     * @throws Exception
     */
    public static function checkStreamingToBrowserIsAllowed(): void
    {
        if (Request::isCurrentApiRequestNestedInAnotherApiRequest()) {
            throw new Exception('A report can only be sent to the browser by the top-level request.');
        }
    }

    protected static function sendToBrowser($filename, $extension, $contentType, $content)
    {
        self::checkStreamingToBrowserIsAllowed();

        $filename = ReportRenderer::makeFilenameWithExtension($filename, $extension);

        ProxyHttp::overrideCacheControlHeaders();
        Common::sendHeader('Content-Description: File Transfer');
        Common::sendHeader('Content-Type: ' . $contentType);
        Common::sendHeader('Content-Disposition: attachment; filename="' . str_replace('"', '\'', basename($filename)) . '";');
        Common::sendHeader('Content-Length: ' . strlen($content));

        echo $content;
    }

    protected static function inlineToBrowser($contentType, $content)
    {
        self::checkStreamingToBrowserIsAllowed();

        Common::sendHeader('Content-Type: ' . $contentType);
        echo $content;
    }

    /**
     * Whether the report aggregates its rows by a dimension.
     *
     * A report without a dimension has a single row of metrics rather than one row per
     * dimension value.
     *
     * @param array $reportMetadata
     */
    protected static function isAggregateReport($reportMetadata): bool
    {
        return !empty($reportMetadata['dimension']);
    }

    /**
     * Convert a dimension-less report to a multi-row two-column data table
     *
     * @static
     * @param  $reportMetadata array
     * @param  $report DataTable
     * @param  $reportColumns array
     * @return array DataTable $report & array $columns
     */
    protected static function processTableFormat($reportMetadata, $report, $reportColumns)
    {
        $finalReport = $report;
        if (!self::isAggregateReport($reportMetadata)) {
            $simpleReportMetrics = $report->getFirstRow();
            if ($simpleReportMetrics) {
                $finalReport = new Simple();
                foreach ($simpleReportMetrics->getColumns() as $metricId => $metric) {
                    $newRow = new Row();
                    $newRow->addColumn("label", $reportColumns[$metricId]);
                    $newRow->addColumn("value", $metric);
                    $finalReport->addRow($newRow);
                }
            }

            $reportColumns = [
                'label' => Piwik::translate('General_Name'),
                'value' => Piwik::translate('General_Value'),
            ];
        }

        return [
            $finalReport,
            $reportColumns,
        ];
    }

    /**
     * Renames the percent of the report total columns of a report to their translation, eg.
     * `nb_visits_percent_of_total` to `Visits (%)`, and moves each of them next to the metric it
     * belongs to.
     *
     * Renderers that build their header from the column names of the report data itself, instead of
     * using the translations and the column order in `$processedReport['columns']`, need this to
     * show those columns the way the other renderers do.
     *
     * @param array $reportColumns column name => translation
     */
    protected static function translatePercentOfTotalColumns(DataTableInterface $report, array $reportColumns): void
    {
        $suffixLength   = strlen(PercentOfReportTotal::COLUMN_NAME_SUFFIX);
        $percentColumns = [];

        foreach ($reportColumns as $columnName => $translation) {
            if (str_ends_with($columnName, PercentOfReportTotal::COLUMN_NAME_SUFFIX)) {
                $percentColumns[substr($columnName, 0, -$suffixLength)] = $translation;
            }
        }

        if (empty($percentColumns)) {
            return;
        }

        $report->filter(function (DataTable $table) use ($percentColumns, $suffixLength) {
            foreach ($table->getRows() as $row) {
                $columns = $row->getColumns();
                $rebuilt = [];

                foreach ($columns as $columnName => $value) {
                    if (
                        str_ends_with($columnName, PercentOfReportTotal::COLUMN_NAME_SUFFIX)
                        && isset($percentColumns[substr($columnName, 0, -$suffixLength)])
                    ) {
                        continue; // emitted below, right after the metric it belongs to
                    }

                    $rebuilt[$columnName] = $value;

                    $percentColumnName = $columnName . PercentOfReportTotal::COLUMN_NAME_SUFFIX;
                    if (isset($percentColumns[$columnName]) && array_key_exists($percentColumnName, $columns)) {
                        $rebuilt[$percentColumns[$columnName]] = $columns[$percentColumnName];
                    }
                }

                $row->setColumns($rebuilt);
            }
        });
    }

    public static function getStaticGraph($reportMetadata, $width, $height, $evolution, $segment)
    {
        $imageGraphUrl = $reportMetadata['imageGraphUrl'];

        if ($evolution && !empty($reportMetadata['imageGraphEvolutionUrl'])) {
            $imageGraphUrl = $reportMetadata['imageGraphEvolutionUrl'];
        }

        $queryString = Url::getQueryStringFromUrl($imageGraphUrl);
        if (!is_string($queryString) || $queryString === '') {
            $queryString = $imageGraphUrl;
        }

        $requestGraph = UrlHelper::getArrayFromQueryString($queryString);
        $requestGraph['outputType'] = API::GRAPH_OUTPUT_PHP;
        $requestGraph['format'] = 'original';
        $requestGraph['serialize'] = 0;
        $requestGraph['filter_truncate'] = '';
        $requestGraph['width'] = $width;
        $requestGraph['height'] = $height;

        if ($segment != null) {
            $requestGraph['segment'] = urlencode($segment['definition']);
        }

        $request = new Request($requestGraph);

        try {
            $imageGraph = $request->process();

            // Get image data as string
            ob_start();
            imagepng($imageGraph);
            $imageGraphData = ob_get_contents();
            ob_end_clean();
            imagedestroy($imageGraph);

            return $imageGraphData;
        } catch (Exception $e) {
            throw new Exception("ImageGraph API returned an error: " . $e->getMessage() . "\n");
        }
    }
}
