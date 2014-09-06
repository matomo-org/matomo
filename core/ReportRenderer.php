<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\API\Request;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;
use Piwik\DataTable;
use Piwik\Plugins\ImageGraph\API;
use Piwik\BaseFactory;

/**
 * A Report Renderer produces user friendly renderings of any given Piwik report.
 * All new Renderers must be copied in ReportRenderer and added to the $availableReportRenderers.
 */
abstract class ReportRenderer extends BaseFactory
{
    const DEFAULT_REPORT_FONT = 'dejavusans';
    const REPORT_TEXT_COLOR = "68,68,68";
    const REPORT_TITLE_TEXT_COLOR = "126,115,99";
    const TABLE_HEADER_BG_COLOR = "228,226,215";
    const TABLE_HEADER_TEXT_COLOR = "37,87,146";
    const TABLE_CELL_BORDER_COLOR = "231,231,231";
    const TABLE_BG_COLOR = "249,250,250";

    const HTML_FORMAT = 'html';
    const PDF_FORMAT = 'pdf';
    const CSV_FORMAT = 'csv';

    private static $availableReportRenderers = array(
        self::PDF_FORMAT,
        self::HTML_FORMAT,
        self::CSV_FORMAT,
    );

    protected static function getClassNameFromClassId($rendererType)
    {
        return 'Piwik\ReportRenderer\\' . self::normalizeRendererType($rendererType);
    }

    protected static function getInvalidClassIdExceptionMessage($rendererType)
    {
        return Piwik::translate(
            'General_ExceptionInvalidReportRendererFormat',
            array(self::normalizeRendererType($rendererType), implode(', ', self::$availableReportRenderers))
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
     * Multiple calls to this method before calling outputRendering appends each report content.
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
    protected static function appendExtension($filename, $extension)
    {
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
        $outputFilename = PIWIK_USER_PATH . '/tmp/assets/' . $filename;
        $outputFilename = SettingsPiwik::rewriteTmpPathWithInstanceId($outputFilename);

        @chmod($outputFilename, 0600);
        @unlink($outputFilename);
        return $outputFilename;
    }

    protected static function writeFile($filename, $extension, $content)
    {
        $filename = self::appendExtension($filename, $extension);
        $outputFilename = self::getOutputPath($filename);

        $emailReport = @fopen($outputFilename, "w");

        if (!$emailReport) {
            throw new Exception ("The file : " . $outputFilename . " can not be opened in write mode.");
        }

        fwrite($emailReport, $content);
        fclose($emailReport);

        return $outputFilename;
    }

    protected static function sendToBrowser($filename, $extension, $contentType, $content)
    {
        $filename = ReportRenderer::appendExtension($filename, $extension);

        ProxyHttp::overrideCacheControlHeaders();
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '\'', basename($filename)) . '";');
        header('Content-Length: ' . strlen($content));

        echo $content;
    }

    protected static function inlineToBrowser($contentType, $content)
    {
        header('Content-Type: ' . $contentType);
        echo $content;
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
        if (empty($reportMetadata['dimension'])) {
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

            $reportColumns = array(
                'label' => Piwik::translate('General_Name'),
                'value' => Piwik::translate('General_Value'),
            );
        }

        return array(
            $finalReport,
            $reportColumns,
        );
    }

    public static function getStaticGraph($reportMetadata, $width, $height, $evolution, $segment)
    {
        $imageGraphUrl = $reportMetadata['imageGraphUrl'];

        if ($evolution && !empty($reportMetadata['imageGraphEvolutionUrl'])) {
            $imageGraphUrl = $reportMetadata['imageGraphEvolutionUrl'];
        }

        $requestGraph = $imageGraphUrl .
            '&outputType=' . API::GRAPH_OUTPUT_PHP .
            '&format=original&serialize=0' .
            '&filter_truncate=' .
            '&width=' . $width .
            '&height=' . $height .
            ($segment != null ? '&segment=' . urlencode($segment['definition']) : '');

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
