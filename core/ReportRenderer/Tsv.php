<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ReportRenderer;

use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Renderer\Tsv as TsvDataTableRenderer;
use Piwik\Piwik;
use Piwik\ReportRenderer;

/**
 * TSV report renderer
 */
class Tsv extends ReportRenderer
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
            ReportRenderer::TSV_FORMAT,
            $this->getRenderedReport()
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
            ReportRenderer::TSV_FORMAT,
            "text/" . ReportRenderer::TSV_FORMAT,
            $this->getRenderedReport()
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
            ReportRenderer::TSV_FORMAT,
            "application/" . ReportRenderer::TSV_FORMAT,
            $this->getRenderedReport()
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
        $tsvRenderer = $this->getRenderer(
            $processedReport['reportData'],
            $processedReport['metadata']['uniqueId']
        );

        $reportData = $tsvRenderer->render();
        if (empty($reportData)) {
            $reportData = Piwik::translate('CoreHome_ThereIsNoDataForThisReport');
        }

        $replaceBySpace = array( $tsvRenderer->separator);
        $reportName = str_replace($replaceBySpace, " ", $processedReport['metadata']['name']);
        $this->rendered .= implode(
            '',
            array(
                $reportName,
                $tsvRenderer->lineEnd,
                $reportData,
                $tsvRenderer->lineEnd,
                $tsvRenderer->lineEnd,
            )
        );
    }

    /**
     * @param DataTableInterface $table
     * @param string $uniqueId
     * @return \Piwik\DataTable\Renderer\Tsv
     */
    protected function getRenderer(DataTableInterface $table, $uniqueId)
    {
        $tsvRenderer = new TsvDataTableRenderer();
        $tsvRenderer->setIdSite($this->idSite);
        $tsvRenderer->setTable($table);
        $tsvRenderer->setConvertToUnicode(false);
        $tsvRenderer->setApiMethod(
            $this->getApiMethodNameFromUniqueId($uniqueId)
        );

        return $tsvRenderer;
    }

    /**
     * @param $uniqueId
     * @return string
     */
    protected function getApiMethodNameFromUniqueId($uniqueId)
    {
        return str_replace("_", ".", $uniqueId);
    }

    /**
     * Get report attachments, ex. graph images
     *
     * @param $report
     * @param $processedReports
     * @param $prettyDate
     * @return array
     */
    public function getAttachments($report, $processedReports, $prettyDate)
    {
        return array();
    }
}
