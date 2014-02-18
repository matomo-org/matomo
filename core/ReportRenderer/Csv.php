<?php
namespace Piwik\ReportRenderer;

use Piwik\ReportRenderer;
use Piwik\DataTable\Renderer\Csv as CsvDataTableRenderer;
use Piwik\DataTable\DataTableInterface;

class Csv extends ReportRenderer
{
    /**
     * @var string
     */
    protected $rendered;

    /**
     * Initialize locale settings.
     * If not called, locale settings defaults to 'en'
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        return;
    }

    /**
     * Save rendering to disk
     *
     * @param string $filename without path & without format extension
     * @return string path of file
     */
    public function sendToDisk($filename)
    {
        return ReportRenderer::writeFile(
            $filename,
            ReportRenderer::CSV_FORMAT,
            $this->rendered
        );
    }

    /**
     * Send rendering to browser with a 'download file' prompt
     *
     * @param string $filename without path & without format extension
     */
    public function sendToBrowserDownload($filename)
    {
        ReportRenderer::sendToBrowser(
            $filename,
            ReportRenderer::CSV_FORMAT,
            "text/" . ReportRenderer::CSV_FORMAT,
            $this->rendered
        );
    }

    /**
     * Output rendering to browser
     *
     * @param string $filename without path & without format extension
     */
    public function sendToBrowserInline($filename)
    {
        ReportRenderer::sendToBrowser(
            $filename,
            ReportRenderer::CSV_FORMAT,
            "application/" . ReportRenderer::CSV_FORMAT,
            $this->rendered
        );
    }

    /**
     * Get rendered report
     */
    public function getRenderedReport()
    {
        return $this->rendered;
    }

    /**
     * Generate the first page.
     *
     * @param string $reportTitle
     * @param string $prettyDate formatted date
     * @param string $description
     * @param array $reportMetadata metadata for all reports
     * @param array $segment segment applied to all reports
     */
    public function renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment)
    {
        return;
    }

    /**
     * Render the provided report.
     * Multiple calls to this method before calling outputRendering appends each report content.
     *
     * @param array $processedReport @see API::getProcessedReport()
     */
    public function renderReport($processedReport)
    {

        $csvRenderer = $this->getRenderer(
            $processedReport['reportData'],
            $processedReport['metadata']['uniqueId']
        );

        $this->rendered .= implode(
            '',
            array(
                $processedReport['metadata']['name'],
                $csvRenderer->lineEnd,
                $csvRenderer->render($processedReport),
                $csvRenderer->lineEnd,
                $csvRenderer->lineEnd,
            )
        );
    }

    /**
     * @param DataTableInterface $table
     * @param string $uniqueId
     * @return \Piwik\DataTable\Renderer\Csv
     */
    protected function getRenderer(DataTableInterface $table, $uniqueId)
    {
        $csvRenderer = new CsvDataTableRenderer();
        $csvRenderer->setTable($table);
        $csvRenderer->setConvertToUnicode(false);
        $csvRenderer->setApiMethod(
            $this->getApiMethodNameFromUniqueId($uniqueId)
        );

        return $csvRenderer;
    }

    /**
     * @param $uniqueId
     * @return string
     */
    protected function getApiMethodNameFromUniqueId($uniqueId)
    {
        return str_replace("_", ".", $uniqueId);
    }
}
