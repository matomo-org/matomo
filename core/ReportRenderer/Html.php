<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_ReportRenderer
 */


/**
 *
 * @package Piwik_ReportRenderer
 */
class Piwik_ReportRenderer_Html extends Piwik_ReportRenderer
{
    const IMAGE_GRAPH_WIDTH = 700;
    const IMAGE_GRAPH_HEIGHT = 200;

    const REPORT_TITLE_TEXT_SIZE = 11;
    const REPORT_TABLE_HEADER_TEXT_SIZE = 11;
    const REPORT_TABLE_ROW_TEXT_SIZE = 11;
    const REPORT_BACK_TO_TOP_TEXT_SIZE = 9;

    const HTML_CONTENT_TYPE = 'text/html';
    const HTML_FILE_EXTENSION = 'html';

    protected $renderImageInline = false;

    private $rendering = "";

    public function setLocale($locale)
    {
        //Nothing to do
    }

    /**
     * Currently only used for HTML reports.
     * When sent by mail, images are attached to the mail: renderImageInline = false
     * When downloaded, images are included base64 encoded in the report body: renderImageInline = true
     *
     * @param boolean $renderImageInline
     */
    public function setRenderImageInline($renderImageInline)
    {
        $this->renderImageInline = $renderImageInline;
    }

    public function sendToDisk($filename)
    {
        $this->epilogue();

        return Piwik_ReportRenderer::writeFile($filename, self::HTML_FILE_EXTENSION, $this->rendering);
    }

    public function sendToBrowserDownload($filename)
    {
        $this->epilogue();

        Piwik_ReportRenderer::sendToBrowser($filename, self::HTML_FILE_EXTENSION, self::HTML_CONTENT_TYPE, $this->rendering);
    }

    public function sendToBrowserInline($filename)
    {
        $this->epilogue();

        Piwik_ReportRenderer::inlineToBrowser(self::HTML_CONTENT_TYPE, $this->rendering);
    }

    public function getRenderedReport()
    {
        $this->epilogue();

        return $this->rendering;
    }

    private function epilogue()
    {
        $smarty = new Piwik_Smarty();
        $this->rendering .= $smarty->fetch(self::prefixTemplatePath("html_report_footer.tpl"));
    }

    public function renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment)
    {
        $smarty = new Piwik_Smarty();
        $this->assignCommonParameters($smarty);

        // todo rename 'websiteName' to 'reportTitle' once branch twig is merged
        $smarty->assign("websiteName", $reportTitle);
        $smarty->assign("prettyDate", $prettyDate);
        $smarty->assign("description", $description);
        $smarty->assign("reportMetadata", $reportMetadata);

        // segment
        $displaySegment = ($segment != null);
        $smarty->assign("displaySegment", $displaySegment);
        if ($displaySegment) {
            $smarty->assign("segmentName", $segment['name']);
        }

        $this->rendering .= $smarty->fetch(self::prefixTemplatePath("html_report_header.tpl"));
    }

    private function assignCommonParameters($smarty)
    {
        $smarty->assign("reportTitleTextColor", Piwik_ReportRenderer::REPORT_TITLE_TEXT_COLOR);
        $smarty->assign("reportTitleTextSize", self::REPORT_TITLE_TEXT_SIZE);
        $smarty->assign("reportTextColor", Piwik_ReportRenderer::REPORT_TEXT_COLOR);
        $smarty->assign("tableHeaderBgColor", Piwik_ReportRenderer::TABLE_HEADER_BG_COLOR);
        $smarty->assign("tableHeaderTextColor", Piwik_ReportRenderer::TABLE_HEADER_TEXT_COLOR);
        $smarty->assign("tableCellBorderColor", Piwik_ReportRenderer::TABLE_CELL_BORDER_COLOR);
        $smarty->assign("tableBgColor", Piwik_ReportRenderer::TABLE_BG_COLOR);
        $smarty->assign("reportTableHeaderTextSize", self::REPORT_TABLE_HEADER_TEXT_SIZE);
        $smarty->assign("reportTableRowTextSize", self::REPORT_TABLE_ROW_TEXT_SIZE);
        $smarty->assign("reportBackToTopTextSize", self::REPORT_BACK_TO_TOP_TEXT_SIZE);
        $smarty->assign("currentPath", Piwik::getPiwikUrl());
        $smarty->assign("logoHeader", Piwik_API_API::getInstance()->getHeaderLogoUrl());
    }

    public function renderReport($processedReport)
    {
        $smarty = new Piwik_Smarty();
        $this->assignCommonParameters($smarty);

        $reportMetadata = $processedReport['metadata'];
        $reportData = $processedReport['reportData'];
        $columns = $processedReport['columns'];
        list($reportData, $columns) = self::processTableFormat($reportMetadata, $reportData, $columns);

        $smarty->assign("reportName", $reportMetadata['name']);
        $smarty->assign("reportId", $reportMetadata['uniqueId']);
        $smarty->assign("reportColumns", $columns);
        $smarty->assign("reportRows", $reportData->getRows());
        $smarty->assign("reportRowsMetadata", $processedReport['reportMetadata']->getRows());
        $smarty->assign("displayTable", $processedReport['displayTable']);

        $displayGraph = $processedReport['displayGraph'];
        $evolutionGraph = $processedReport['evolutionGraph'];
        $smarty->assign("displayGraph", $displayGraph);

        if ($displayGraph) {
            $smarty->assign("graphWidth", self::IMAGE_GRAPH_WIDTH);
            $smarty->assign("graphHeight", self::IMAGE_GRAPH_HEIGHT);
            $smarty->assign("renderImageInline", $this->renderImageInline);

            if ($this->renderImageInline) {
                $staticGraph = parent::getStaticGraph(
                    $reportMetadata,
                    self::IMAGE_GRAPH_WIDTH,
                    self::IMAGE_GRAPH_HEIGHT,
                    $evolutionGraph,
                    $processedReport['segment']
                );
                $smarty->assign("generatedImageGraph", base64_encode($staticGraph));
                unset($generatedImageGraph);
            }
        }

        $this->rendering .= $smarty->fetch(self::prefixTemplatePath("html_report_body.tpl"));
    }

    private static function prefixTemplatePath($templateFile)
    {
        return PIWIK_USER_PATH . "/plugins/CoreHome/templates/" . $templateFile;
    }
}