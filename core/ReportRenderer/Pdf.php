<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ReportRenderer;

use Piwik\Common;
use Piwik\Filesystem;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugins\API\API;
use Piwik\ReportRenderer;
use Piwik\TCPDF;

/**
 * @see libs/tcpdf
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/ScheduledReports/config/tcpdf_config.php';

/**
 * PDF report renderer
 */
class Pdf extends ReportRenderer
{
    const IMAGE_GRAPH_WIDTH_LANDSCAPE = 1050;
    const IMAGE_GRAPH_WIDTH_PORTRAIT = 760;
    const IMAGE_GRAPH_HEIGHT = 220;

    const LANDSCAPE = 'L';
    const PORTRAIT = 'P';

    const MAX_ROW_COUNT = 28;
    const TABLE_HEADER_ROW_COUNT = 6;
    const NO_DATA_ROW_COUNT = 6;
    const MAX_GRAPH_REPORTS = 3;
    const MAX_2COL_TABLE_REPORTS = 2;

    const PDF_CONTENT_TYPE = 'pdf';

    private $reportFontStyle = '';
    private $reportSimpleFontSize = 9;
    private $reportHeaderFontSize = 16;
    private $cellHeight = 6;
    private $bottomMargin = 17;
    private $reportWidthPortrait = 195;
    private $reportWidthLandscape = 270;
    private $minWidthLabelCell = 100;
    private $maxColumnCountPortraitOrientation = 6;
    private $logoWidth = 16;
    private $logoHeight = 16;
    private $totalWidth;
    private $cellWidth;
    private $labelCellWidth;
    private $truncateAfter = 55;
    private $leftSpacesBeforeLogo = 7;
    private $logoImagePosition = array(10, 40);
    private $headerTextColor;
    private $reportTextColor;
    private $tableHeaderBackgroundColor;
    private $tableHeaderTextColor;
    private $tableCellBorderColor;
    private $tableBackgroundColor;
    private $rowTopBottomBorder = array(231, 231, 231);
    private $report;
    private $reportMetadata;
    private $displayGraph;
    private $evolutionGraph;
    private $displayTable;
    private $segment;
    private $reportColumns;
    private $reportRowsMetadata;
    private $currentPage = 0;
    private $reportFont = ReportRenderer::DEFAULT_REPORT_FONT_FAMILY;
    private $TCPDF;
    private $orientation = self::PORTRAIT;

    public function __construct()
    {
        $this->TCPDF = new TCPDF();
        $this->headerTextColor = preg_split("/,/", ReportRenderer::REPORT_TITLE_TEXT_COLOR);
        $this->reportTextColor = preg_split("/,/", ReportRenderer::REPORT_TEXT_COLOR);
        $this->tableHeaderBackgroundColor = preg_split("/,/", ReportRenderer::TABLE_HEADER_BG_COLOR);
        $this->tableHeaderTextColor = preg_split("/,/", ReportRenderer::TABLE_HEADER_TEXT_COLOR);
        $this->tableCellBorderColor = preg_split("/,/", ReportRenderer::TABLE_CELL_BORDER_COLOR);
        $this->tableBackgroundColor = preg_split("/,/", ReportRenderer::TABLE_BG_COLOR);
    }

    public function setLocale($locale)
    {
        // WARNING
        // To make Piwik release smaller, we're deleting some fonts from the Piwik build package.
        // If you change this code below, make sure that the fonts are NOT deleted from the Piwik package:
        // https://github.com/piwik/piwik-package/blob/master/scripts/build-package.sh
        switch ($locale) {
            case 'bn':
            case 'hi':
                $reportFont = 'freesans';
                break;

            case 'zh-tw':
                $reportFont = 'msungstdlight';
                break;

            case 'ja':
                $reportFont = 'kozgopromedium';
                break;

            case 'zh-cn':
                $reportFont = 'stsongstdlight';
                break;

            case 'ko':
                $reportFont = 'hysmyeongjostdmedium';
                break;

            case 'ar':
                $reportFont = 'aealarabiya';
                break;

            case 'am':
            case 'ta':
            case 'th':
                $reportFont = 'freeserif';
                break;

            case 'te':
                // not working with bundled fonts
            case 'en':
            default:
                $reportFont = ReportRenderer::DEFAULT_REPORT_FONT_FAMILY;
                break;
        }
        // WARNING: Did you read the warning above?

        $this->reportFont = $reportFont;
    }

    public function sendToDisk($filename)
    {
        $filename = ReportRenderer::makeFilenameWithExtension($filename, self::PDF_CONTENT_TYPE);
        $outputFilename = ReportRenderer::getOutputPath($filename);

        $this->TCPDF->Output($outputFilename, 'F');

        return $outputFilename;
    }

    public function sendToBrowserDownload($filename)
    {
        $filename = ReportRenderer::makeFilenameWithExtension($filename, self::PDF_CONTENT_TYPE);
        $this->TCPDF->Output($filename, 'D');
    }

    public function sendToBrowserInline($filename)
    {
        $filename = ReportRenderer::makeFilenameWithExtension($filename, self::PDF_CONTENT_TYPE);
        $this->TCPDF->Output($filename, 'I');
    }

    public function getRenderedReport()
    {
        return $this->TCPDF->Output(null, 'S');
    }

    public function renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment)
    {
        $reportTitle = $this->formatText($reportTitle);
        $dateRange = $this->formatText(Piwik::translate('General_DateRange') . " " . $prettyDate);

        // footer
        $this->TCPDF->SetFooterFont(array($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize));
        $this->TCPDF->SetFooterContent($reportTitle . " | " . $dateRange . " | ");

        // add first page
        $this->TCPDF->setPrintHeader(false);
        $this->TCPDF->AddPage(self::PORTRAIT);
        $this->TCPDF->AddFont($this->reportFont, '', '', false);
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);
        $this->TCPDF->Bookmark(Piwik::translate('ScheduledReports_FrontPage'));

        // logo
        $this->TCPDF->Image(API::getInstance()->getLogoUrl(true), $this->logoImagePosition[0], $this->logoImagePosition[1], 180 / $factor = 2, 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300);
        $this->TCPDF->Ln(8);

        // report title
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize + 5);
        $this->TCPDF->SetTextColor($this->headerTextColor[0], $this->headerTextColor[1], $this->headerTextColor[2]);
        $this->TCPDF->Cell(40, 210, $reportTitle);
        $this->TCPDF->Ln(8 * 4);

        // date and period
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
        $this->TCPDF->Cell(40, 210, $dateRange);
        $this->TCPDF->Ln(8 * 20);

        // description
        $this->TCPDF->Write(1, $this->formatText($description));

        // segment
        if ($segment != null) {
            $this->TCPDF->Ln();
            $this->TCPDF->Ln();
            $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize - 2);
            $this->TCPDF->SetTextColor($this->headerTextColor[0], $this->headerTextColor[1], $this->headerTextColor[2]);
            $this->TCPDF->Write(1, $this->formatText(Piwik::translate('ScheduledReports_CustomVisitorSegment') . ' ' . $segment['name']));
        }

        $this->TCPDF->Ln(8);
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize);
        $this->TCPDF->Ln();
    }

    /**
     * Generate a header of page.
     */
    private function paintReportHeader()
    {
        $isAggregateReport = !empty($this->reportMetadata['dimension']);

        // Graph-only report
        static $graphOnlyReportCount = 0;
        $graphOnlyReport = $isAggregateReport && $this->displayGraph && !$this->displayTable;

        // Table-only report
        $tableOnlyReport = $isAggregateReport
            && !$this->displayGraph
            && $this->displayTable;

        $columnCount = count($this->reportColumns);

        // Table-only 2-column report
        static $tableOnly2ColumnReportCount = 0;
        $tableOnly2ColumnReport = $tableOnlyReport
            && $columnCount == 2;

        // Table-only report with more than 2 columns
        static $tableOnlyManyColumnReportRowCount = 0;
        $tableOnlyManyColumnReport = $tableOnlyReport
            && $columnCount > 3;

        $reportHasData = $this->reportHasData();

        $rowCount = $reportHasData ? $this->report->getRowsCount() + self::TABLE_HEADER_ROW_COUNT : self::NO_DATA_ROW_COUNT;

        // Only a page break before if the current report has some data
        if ($reportHasData &&
            // and
            (
                // it is the first report
                $this->currentPage == 0
                // or, it is a graph-only report and it is the first of a series of self::MAX_GRAPH_REPORTS
                || ($graphOnlyReport && $graphOnlyReportCount == 0)
                // or, it is a table-only 2-column report and it is the first of a series of self::MAX_2COL_TABLE_REPORTS
                || ($tableOnly2ColumnReport && $tableOnly2ColumnReportCount == 0)
                // or it is a table-only report with more than 2 columns and it is the first of its series or there isn't enough space left on the page
                || ($tableOnlyManyColumnReport && ($tableOnlyManyColumnReportRowCount == 0 || $tableOnlyManyColumnReportRowCount + $rowCount >= self::MAX_ROW_COUNT))
                // or it is a report with both a table and a graph
                || !$graphOnlyReport && !$tableOnlyReport
            )
        ) {
            $this->currentPage++;
            $this->TCPDF->AddPage();

            // Table-only reports with more than 2 columns are always landscape
            if ($tableOnlyManyColumnReport) {
                $tableOnlyManyColumnReportRowCount = 0;
                $this->orientation = self::LANDSCAPE;
            } else {
                // Graph-only reports are always portrait
                $this->orientation = $graphOnlyReport ? self::PORTRAIT : ($columnCount > $this->maxColumnCountPortraitOrientation ? self::LANDSCAPE : self::PORTRAIT);
            }

            $this->TCPDF->setPageOrientation($this->orientation, '', $this->bottomMargin);
        }

        $graphOnlyReportCount = ($graphOnlyReport && $reportHasData) ? ($graphOnlyReportCount + 1) % self::MAX_GRAPH_REPORTS : 0;
        $tableOnly2ColumnReportCount = ($tableOnly2ColumnReport && $reportHasData) ? ($tableOnly2ColumnReportCount + 1) % self::MAX_2COL_TABLE_REPORTS : 0;
        $tableOnlyManyColumnReportRowCount = $tableOnlyManyColumnReport ? ($tableOnlyManyColumnReportRowCount + $rowCount) : 0;

        $title = $this->formatText($this->reportMetadata['name']);
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportHeaderFontSize);
        $this->TCPDF->SetTextColor($this->headerTextColor[0], $this->headerTextColor[1], $this->headerTextColor[2]);
        $this->TCPDF->Bookmark($title);
        $this->TCPDF->Cell(40, 15, $title);
        $this->TCPDF->Ln();
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportSimpleFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
    }

    private function reportHasData()
    {
        return $this->report->getRowsCount() > 0;
    }

    private function setBorderColor()
    {
        $this->TCPDF->SetDrawColor($this->tableCellBorderColor[0], $this->tableCellBorderColor[1], $this->tableCellBorderColor[2]);
    }

    public function renderReport($processedReport)
    {
        $this->reportMetadata = $processedReport['metadata'];
        $this->reportRowsMetadata = $processedReport['reportMetadata'];
        $this->displayGraph = $processedReport['displayGraph'];
        $this->evolutionGraph = $processedReport['evolutionGraph'];
        $this->displayTable = $processedReport['displayTable'];
        $this->segment = $processedReport['segment'];
        list($this->report, $this->reportColumns) = self::processTableFormat($this->reportMetadata, $processedReport['reportData'], $processedReport['columns']);

        $this->paintReportHeader();

        if (!$this->reportHasData()) {
            $this->paintMessage(Piwik::translate('CoreHome_ThereIsNoDataForThisReport'));
            return;
        }

        if ($this->displayGraph) {
            $this->paintGraph();
        }

        if ($this->displayGraph && $this->displayTable) {
            $this->TCPDF->Ln(5);
        }

        if ($this->displayTable) {
            $this->paintReportTableHeader();
            $this->paintReportTable();
        }
    }

    private function formatText($text)
    {
        return Common::unsanitizeInputValue($text);
    }

    private function paintReportTable()
    {
        //Color and font restoration
        $this->TCPDF->SetFillColor($this->tableBackgroundColor[0], $this->tableBackgroundColor[1], $this->tableBackgroundColor[2]);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
        $this->TCPDF->SetFont('');

        $fill = true;
        $url = false;
        $leftSpacesBeforeLogo = str_repeat(' ', $this->leftSpacesBeforeLogo);

        $logoWidth = $this->logoWidth;
        $logoHeight = $this->logoHeight;

        $rowsMetadata = $this->reportRowsMetadata->getRows();

        // Draw a body of report table
        foreach ($this->report->getRows() as $rowId => $row) {
            $rowMetrics = $row->getColumns();
            $rowMetadata = isset($rowsMetadata[$rowId]) ? $rowsMetadata[$rowId]->getColumns() : array();
            if (isset($rowMetadata['url'])) {
                $url = $rowMetadata['url'];
            }
            foreach ($this->reportColumns as $columnId => $columnName) {
                // Label column
                if ($columnId == 'label') {
                    $isLogoDisplayable = isset($rowMetadata['logo']);
                    $text = '';
                    $posX = $this->TCPDF->GetX();
                    $posY = $this->TCPDF->GetY();
                    if (isset($rowMetrics[$columnId])) {
                        $text = substr($rowMetrics[$columnId], 0, $this->truncateAfter);
                        if ($isLogoDisplayable) {
                            $text = $leftSpacesBeforeLogo . $text;
                        }
                    }
                    $text = $this->formatText($text);

                    $this->TCPDF->Cell($this->labelCellWidth, $this->cellHeight, $text, 'LR', 0, 'L', $fill, $url);

                    if ($isLogoDisplayable) {
                        if (isset($rowMetadata['logoWidth'])) {
                            $logoWidth = $rowMetadata['logoWidth'];
                        }
                        if (isset($rowMetadata['logoHeight'])) {
                            $logoHeight = $rowMetadata['logoHeight'];
                        }
                        $restoreY = $this->TCPDF->getY();
                        $restoreX = $this->TCPDF->getX();
                        $this->TCPDF->SetY($posY);
                        $this->TCPDF->SetX($posX);
                        $topMargin = 1.3;
                        // Country flags are not very high, force a bigger top margin
                        if ($logoHeight < 16) {
                            $topMargin = 2;
                        }
                        $path = Filesystem::getPathToPiwikRoot() . "/" . $rowMetadata['logo'];
                        if (file_exists($path)) {
                            $this->TCPDF->Image($path, $posX + ($leftMargin = 2), $posY + $topMargin, $logoWidth / 4);
                        }
                        $this->TCPDF->SetXY($restoreX, $restoreY);
                    }
                } // metrics column
                else {
                    // No value means 0
                    if (empty($rowMetrics[$columnId])) {
                        $rowMetrics[$columnId] = 0;
                    }
                    $this->TCPDF->Cell($this->cellWidth, $this->cellHeight, NumberFormatter::getInstance()->format($rowMetrics[$columnId]), 'LR', 0, 'L', $fill);
                }
            }

            $this->TCPDF->Ln();

            // Top/Bottom grey border for all cells
            $this->TCPDF->SetDrawColor($this->rowTopBottomBorder[0], $this->rowTopBottomBorder[1], $this->rowTopBottomBorder[2]);
            $this->TCPDF->Cell($this->totalWidth, 0, '', 'T');
            $this->setBorderColor();
            $this->TCPDF->Ln(0.2);

            $fill = !$fill;
        }
    }

    private function paintGraph()
    {
        $imageGraph = parent::getStaticGraph(
            $this->reportMetadata,
            $this->orientation == self::PORTRAIT ? self::IMAGE_GRAPH_WIDTH_PORTRAIT : self::IMAGE_GRAPH_WIDTH_LANDSCAPE,
            self::IMAGE_GRAPH_HEIGHT,
            $this->evolutionGraph,
            $this->segment
        );

        $this->TCPDF->Image(
            '@' . $imageGraph,
            $x = '',
            $y = '',
            $w = 0,
            $h = 0,
            $type = '',
            $link = '',
            $align = 'N',
            $resize = false,
            $dpi = 72,
            $palign = '',
            $ismask = false,
            $imgmask = false,
            $order = 0,
            $fitbox = false,
            $hidden = false,
            $fitonpage = true,
            $alt = false,
            $altimgs = array()
        );

        unset($imageGraph);
    }

    /**
     * Draw the table header (first row)
     */
    private function paintReportTableHeader()
    {
        $initPosX = 10;

        // Get the longest column name
        $longestColumnName = '';
        foreach ($this->reportColumns as $columnName) {
            if (strlen($columnName) > strlen($longestColumnName)) {
                $longestColumnName = $columnName;
            }
        }

        $columnsCount = count($this->reportColumns);
        // Computes available column width
        if ($this->orientation == self::PORTRAIT
            && $columnsCount <= 3
        ) {
            $totalWidth = $this->reportWidthPortrait * 2 / 3;
        } elseif ($this->orientation == self::LANDSCAPE) {
            $totalWidth = $this->reportWidthLandscape;
        } else {
            $totalWidth = $this->reportWidthPortrait;
        }
        $this->totalWidth = $totalWidth;
        $this->labelCellWidth = max(round(($this->totalWidth / $columnsCount)), $this->minWidthLabelCell);
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / ($columnsCount - 1));
        $this->totalWidth = $this->labelCellWidth + ($columnsCount - 1) * $this->cellWidth;

        $this->TCPDF->SetFillColor($this->tableHeaderBackgroundColor[0], $this->tableHeaderBackgroundColor[1], $this->tableHeaderBackgroundColor[2]);
        $this->TCPDF->SetTextColor($this->tableHeaderTextColor[0], $this->tableHeaderTextColor[1], $this->tableHeaderTextColor[2]);
        $this->TCPDF->SetLineWidth(.3);
        $this->setBorderColor();
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle);
        $this->TCPDF->SetFillColor(255);
        $this->TCPDF->SetTextColor($this->tableHeaderBackgroundColor[0], $this->tableHeaderBackgroundColor[1], $this->tableHeaderBackgroundColor[2]);
        $this->TCPDF->SetDrawColor(255);

        $posY = $this->TCPDF->GetY();
        $this->TCPDF->MultiCell($this->cellWidth, $this->cellHeight, $longestColumnName, 1, 'C', true);
        $maxCellHeight = $this->TCPDF->GetY() - $posY;

        $this->TCPDF->SetFillColor($this->tableHeaderBackgroundColor[0], $this->tableHeaderBackgroundColor[1], $this->tableHeaderBackgroundColor[2]);
        $this->TCPDF->SetTextColor($this->tableHeaderTextColor[0], $this->tableHeaderTextColor[1], $this->tableHeaderTextColor[2]);
        $this->TCPDF->SetDrawColor($this->tableCellBorderColor[0], $this->tableCellBorderColor[1], $this->tableCellBorderColor[2]);

        $this->TCPDF->SetXY($initPosX, $posY);

        $countColumns = 0;
        $posX = $initPosX;
        foreach ($this->reportColumns as $columnName) {
            $columnName = $this->formatText($columnName);

            //Label column
            if ($countColumns == 0) {
                $this->TCPDF->MultiCell($this->labelCellWidth, $maxCellHeight, $columnName, $border = 0, $align = 'L', true);
                $this->TCPDF->SetXY($posX + $this->labelCellWidth, $posY);
            } else {
                $this->TCPDF->MultiCell($this->cellWidth, $maxCellHeight, $columnName, $border = 0, $align = 'L', true);
                $this->TCPDF->SetXY($posX + $this->cellWidth, $posY);
            }
            $countColumns++;
            $posX = $this->TCPDF->GetX();
        }
        $this->TCPDF->Ln();
        $this->TCPDF->SetXY($initPosX, $posY + $maxCellHeight);
    }

    /**
     * Prints a message
     *
     * @param string $message
     * @return void
     */
    private function paintMessage($message)
    {
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
        $message = $this->formatText($message);
        $this->TCPDF->Write("1em", $message);
        $this->TCPDF->Ln();
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
