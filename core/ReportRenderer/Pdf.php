<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ReportRenderer;

use Piwik\Common;
use Piwik\Filesystem;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\ReportRenderer;
use Piwik\TCPDF;
use TCPDF_FONTS;

/**
 * @see libs/tcpdf
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/ScheduledReports/config/tcpdf_config.php';

/**
 * PDF report renderer
 */
class Pdf extends ReportRenderer
{
    public const IMAGE_GRAPH_WIDTH_PORTRAIT = 760;
    public const IMAGE_GRAPH_HEIGHT = 220;

    public const LANDSCAPE = 'L';
    public const PORTRAIT = 'P';

    public const MAX_ROW_COUNT = 28;
    public const TABLE_HEADER_ROW_COUNT = 6;
    public const NO_DATA_ROW_COUNT = 6;
    public const MAX_GRAPH_REPORTS = 3;
    public const MAX_2COL_TABLE_REPORTS = 2;

    public const IMPORT_FONT_PATH = 'plugins/ImageGraph/fonts/unifont.ttf';

    public const PDF_CONTENT_TYPE = 'pdf';

    private $reportFontStyle = '';
    private $reportSimpleFontSize = 9;
    private $reportHeaderFontSize = 16;
    private $cellHeight = 6;
    private $bottomMargin = 17;
    private $reportWidthPortrait = 195;
    private $minWidthLabelCellPortrait = 80;
    private $minWidthLabelCellPortraitShort = 65;
    private $logoWidth = 16;
    private $logoHeight = 16;
    private $totalWidth;
    private $cellWidth;
    private $labelCellWidth;
    private $truncateAfter = 55;
    private $maxLabelLines = 3;
    private $leftSpacesBeforeLogo = 7;
    private $logoImagePosition = array(10, 40);
    private $headerBottomPadding = 2;
    private $headerBottomPaddingShort = 0.5;
    private $headerTextColor;
    private $reportTextColor;
    private $tableHeaderBackgroundColor;
    private $tableHeaderTextColor;
    private $tableCellBorderColor;
    private $tableBackgroundColor;
    private $rowTopBottomBorder = array(231, 231, 231);
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
    private $labelShortContentThreshold = 100;
    private $columnCellWidths = array();
    private $minMetricColumnWidth = 14;

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

        // When user follow the FAQ https://matomo.org/faq/how-to-install/faq_142/, imported unifont font, it will apply across the entire report
        if (is_file(self::IMPORT_FONT_PATH)) {
            $reportFont = TCPDF_FONTS::addTTFfont(self::IMPORT_FONT_PATH, 'TrueTypeUnicode');
        }
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
        return $this->TCPDF->Output('', 'S');
    }

    public function renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment)
    {
        $reportTitle = $this->formatText($reportTitle);
        $dateRange = $this->formatText(Piwik::translate('General_DateRange') . " " . $prettyDate);

        // footer
        $this->TCPDF->SetFooterFont(array($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize));
        $this->TCPDF->SetFooterContent((strlen($reportTitle) > 64 ? substr($reportTitle, 0, 61) . "..." : $reportTitle) . " | " . $dateRange . " | ");

        // add first page
        $this->TCPDF->setPrintHeader(false);
        $this->TCPDF->AddPage(self::PORTRAIT);
        $this->TCPDF->AddFont($this->reportFont, '', '', false);
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);
        $this->TCPDF->Bookmark(Piwik::translate('ScheduledReports_FrontPage'));

        // logo
        $customLogo = new CustomLogo();
        $this->TCPDF->Image($customLogo->getLogoUrl(true), $this->logoImagePosition[0], $this->logoImagePosition[1], 180 / $factor = 2, 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300);
        $this->TCPDF->Ln(8);

        // report title
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize + 5);
        $this->TCPDF->SetTextColor($this->headerTextColor[0], $this->headerTextColor[1], $this->headerTextColor[2]);
        $this->TCPDF->SetXY(10, 119);
        $this->TCPDF->MultiCell(0, 40, $reportTitle, 0, 'L');

        // date and period
        $this->TCPDF->SetXY(10, 152);
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
        $this->TCPDF->MultiCell(0, 40, $dateRange, 0, 'L');

        // description
        $this->TCPDF->SetXY(10, 210);
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
        if (
            $reportHasData
            // and
            && (
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
            }
            // Scheduled reports should never switch to landscape layouts to keep a consistent portrait output
            $this->orientation = self::PORTRAIT;
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
            $rowHeight = $this->cellHeight;
            foreach ($this->reportColumns as $columnId => $columnName) {
                // Label column
                if ($columnId == 'label') {
                    $isLogoDisplayable = isset($rowMetadata['logo']);
                    $text = '';
                    $posX = $this->TCPDF->GetX();
                    $posY = $this->TCPDF->GetY();
                    if (isset($rowMetrics[$columnId])) {
                        $maxCharacters = $this->truncateAfter * $this->maxLabelLines;
                        $text = mb_substr($rowMetrics[$columnId], 0, $maxCharacters);
                        if ($isLogoDisplayable) {
                            $text = $leftSpacesBeforeLogo . $text;
                        }
                    }
                    $text = $this->formatText($text);
                    $text = $this->truncateLabelTextToLines($text);
                    $rowHeight = $this->getLabelRowHeight($text);
                    $this->TCPDF->MultiCell(
                        $this->labelCellWidth,
                        $this->cellHeight,
                        $text,
                        'LR',
                        'L',
                        $fill,
                        0,
                        '',
                        '',
                        true,
                        0,
                        false,
                        true,
                        $rowHeight,
                        'M'
                    );
                    if ($url) {
                        $this->TCPDF->Link($posX, $posY, $this->labelCellWidth, $rowHeight, $url);
                    }
                    $this->TCPDF->SetXY($posX + $this->labelCellWidth, $posY);

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
                } else {
                    // metrics column

                    // No value means 0
                    if (empty($rowMetrics[$columnId])) {
                        $rowMetrics[$columnId] = 0;
                    }
                    $columnWidth = $this->getColumnWidth($columnId);
                    $this->TCPDF->Cell(
                        $columnWidth,
                        $rowHeight,
                        NumberFormatter::getInstance()->format($rowMetrics[$columnId]),
                        'LR',
                        0,
                        'L',
                        $fill
                    );
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

    private function truncateLabelTextToLines(string $text, ?float $availableWidth = null): string
    {
        $width = $availableWidth ?: $this->labelCellWidth;

        if ($this->TCPDF->getNumLines($text, $width) <= $this->maxLabelLines) {
            return $text;
        }

        $suffix = '...';
        $length = mb_strlen($text);
        $start = 0;
        $end = $length;
        $best = '';

        while ($start <= $end) {
            $mid = (int) floor(($start + $end) / 2);
            $candidate = mb_substr($text, 0, $mid);
            if ($mid < $length) {
                $candidate .= $suffix;
            }
            if ($this->TCPDF->getNumLines($candidate, $width) > $this->maxLabelLines) {
                $end = $mid - 1;
            } else {
                $best = $candidate;
                $start = $mid + 1;
            }
        }

        return $best !== '' ? $best : mb_substr($text, 0, $this->truncateAfter);
    }

    private function getLabelRowHeight(string $text): float
    {
        $maxHeight = $this->cellHeight * $this->maxLabelLines;
        $labelHeight = $this->TCPDF->getStringHeight($this->labelCellWidth, $text);

        if ($labelHeight > $maxHeight) {
            return $maxHeight;
        }

        if ($labelHeight < $this->cellHeight) {
            return $this->cellHeight;
        }

        return $labelHeight;
    }

    private function adjustLabelWidthForTableData(int $columnsCount): void
    {
        if ($columnsCount <= 1 || !$this->reportHasData()) {
            return;
        }

        if (!$this->shouldUseShortLabelWidth()) {
            return;
        }

        $this->labelCellWidth = $this->minWidthLabelCellPortraitShort;
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / ($columnsCount - 1));
        $this->totalWidth = $this->labelCellWidth + ($columnsCount - 1) * $this->cellWidth;
    }

    private function getColumnWidth(string $columnId): float
    {
        if (isset($this->columnCellWidths[$columnId])) {
            return (float) $this->columnCellWidths[$columnId];
        }

        if ($columnId === 'label') {
            return (float) $this->labelCellWidth;
        }

        return (float) $this->cellWidth;
    }

    private function adjustMetricColumnWidthsForRevenue(): void
    {
        if (!$this->reportHasData()) {
            return;
        }

        if (!array_key_exists('revenue', $this->reportColumns) || !isset($this->columnCellWidths['revenue'])) {
            return;
        }

        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);

        $requiredWidth = $this->getMaxFormattedColumnWidth('revenue');
        if ($requiredWidth <= 0) {
            return;
        }

        $currentWidth = $this->columnCellWidths['revenue'];
        if ($requiredWidth <= $currentWidth) {
            return;
        }

        $additionalWidth = $requiredWidth - $currentWidth;
        $remainingWidthToGain = $additionalWidth;

        foreach ($this->columnCellWidths as $columnId => $width) {
            if ($columnId === 'label' || $columnId === 'revenue') {
                continue;
            }
            $availableReduction = $width - $this->minMetricColumnWidth;
            if ($availableReduction <= 0) {
                continue;
            }
            $reduction = min($availableReduction, $remainingWidthToGain);
            if ($reduction <= 0) {
                continue;
            }
            $this->columnCellWidths[$columnId] -= $reduction;
            $remainingWidthToGain -= $reduction;
            if ($remainingWidthToGain <= 0) {
                break;
            }
        }

        $appliedWidth = $additionalWidth - $remainingWidthToGain;
        if ($appliedWidth <= 0) {
            return;
        }

        $this->columnCellWidths['revenue'] += $appliedWidth;
    }

    private function getMaxFormattedColumnWidth(string $columnId): float
    {
        $maxWidth = 0;

        foreach ($this->report->getRows() as $row) {
            $value = $row->getColumn($columnId);
            if ($value === false || $value === null) {
                continue;
            }
            $formattedValue = NumberFormatter::getInstance()->format($value);
            $width = $this->TCPDF->GetStringWidth($formattedValue);
            if ($width > $maxWidth) {
                $maxWidth = $width;
            }
        }

        if ($maxWidth <= 0) {
            return 0;
        }

        return $maxWidth + 2;
    }

    private function shouldUseShortLabelWidth(): bool
    {
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);

        $maxCharacters = $this->truncateAfter * $this->maxLabelLines;

        $rowsMetadata = array();
        if (!empty($this->reportRowsMetadata)) {
            $rowsMetadata = $this->reportRowsMetadata->getRows();
        }

        $maxLength = 0;
        foreach ($this->report->getRows() as $rowId => $row) {
            $label = $row->getColumn('label');
            if ($label === false || $label === null) {
                continue;
            }
            $rawLabel = (string) $label;
            $visibleLabel = mb_substr($rawLabel, 0, $maxCharacters);

            if (mb_strlen($rawLabel) > mb_strlen($visibleLabel)) {
                return false;
            }

            $length = mb_strlen($visibleLabel);
            if ($length > $maxLength) {
                $maxLength = $length;
                if ($maxLength >= $this->labelShortContentThreshold) {
                    return false;
                }
            }
            if ( isset($rowsMetadata[$rowId]) ) {
                $rowMeta = $rowsMetadata[$rowId]->getColumns();
                if (isset($rowMeta['logo'])) {
                    $visibleLabel = str_repeat(' ', $this->leftSpacesBeforeLogo) . $visibleLabel;
                }
            }

            $formattedLabel = $this->formatText($visibleLabel);
            if ($this->TCPDF->getNumLines($formattedLabel, $this->labelCellWidth) > 1) {
                return false;
            }
        }

        return $maxLength > 0;
    }

    private function paintGraph()
    {
        $imageGraph = parent::getStaticGraph(
            $this->reportMetadata,
            $this->orientation == self::IMAGE_GRAPH_WIDTH_PORTRAIT,
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

        $columnsCount = count($this->reportColumns);
        // Computes available column width
        if ( $columnsCount <= 3 ) {
            $totalWidth = $this->reportWidthPortrait * 2 / 3;
        } else {
            $totalWidth = $this->reportWidthPortrait;
        }
        $this->totalWidth = $totalWidth;
        $minLabelWidth =  $this->minWidthLabelCellPortrait;
        $this->labelCellWidth = max(round(($this->totalWidth / $columnsCount)), $minLabelWidth);
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / ($columnsCount - 1));
        $this->totalWidth = $this->labelCellWidth + ($columnsCount - 1) * $this->cellWidth;
        $this->adjustLabelWidthForTableData($columnsCount);
        $this->columnCellWidths = array();
        foreach ($this->reportColumns as $columnId => $columnName) {
            if ($columnId === 'label') {
                $this->columnCellWidths[$columnId] = $this->labelCellWidth;
            } else {
                $this->columnCellWidths[$columnId] = $this->cellWidth;
            }
        }
        $this->adjustMetricColumnWidthsForRevenue();
        $this->totalWidth = array_sum($this->columnCellWidths);

        $this->TCPDF->SetFillColor($this->tableHeaderBackgroundColor[0], $this->tableHeaderBackgroundColor[1], $this->tableHeaderBackgroundColor[2]);
        $this->TCPDF->SetTextColor($this->tableHeaderTextColor[0], $this->tableHeaderTextColor[1], $this->tableHeaderTextColor[2]);
        $this->TCPDF->SetLineWidth(.3);
        $this->setBorderColor();
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle);
        $this->TCPDF->SetFillColor(255);
        $this->TCPDF->SetTextColor($this->tableHeaderBackgroundColor[0], $this->tableHeaderBackgroundColor[1], $this->tableHeaderBackgroundColor[2]);
        $this->TCPDF->SetDrawColor(255);

        $posY = $this->TCPDF->GetY();
        $columnData = array();
        $maxCellHeight = $this->cellHeight;
        $countColumns = 0;
        foreach ($this->reportColumns as $columnId => $columnName) {
            $columnName = $this->formatText($columnName);
            $columnWidth = $this->getColumnWidth($columnId);
            $textHeight = $this->TCPDF->getStringHeight($columnWidth, $columnName);
            if ($textHeight < $this->cellHeight) {
                $textHeight = $this->cellHeight;
            }
            $columnData[] = array(
                'text' => $columnName,
                'width' => $columnWidth,
                'height' => $textHeight,
            );
            if ($textHeight > $maxCellHeight) {
                $maxCellHeight = $textHeight;
            }
            $countColumns++;
        }
        $this->TCPDF->SetXY($initPosX, $posY);

        $this->TCPDF->SetFillColor($this->tableHeaderBackgroundColor[0], $this->tableHeaderBackgroundColor[1], $this->tableHeaderBackgroundColor[2]);
        $this->TCPDF->SetTextColor($this->tableHeaderTextColor[0], $this->tableHeaderTextColor[1], $this->tableHeaderTextColor[2]);
        $this->TCPDF->SetDrawColor($this->tableCellBorderColor[0], $this->tableCellBorderColor[1], $this->tableCellBorderColor[2]);

        $this->TCPDF->SetXY($initPosX, $posY);

        $countColumns = 0;
        $posX = $initPosX;
        foreach ($columnData as $columnInfo) {
            $columnName = $columnInfo['text'];
            $columnWidth = $columnInfo['width'];
            $textHeight = $columnInfo['height'];

            $this->TCPDF->Rect($posX, $posY, $columnWidth, $maxCellHeight, 'F');

            $effectivePadding = $this->headerBottomPadding;
            if ($textHeight <= $this->cellHeight) {
                $effectivePadding = $this->headerBottomPaddingShort;
            }
            $textPosY = $posY + max(0, $maxCellHeight - $textHeight) - $effectivePadding;
            if ($textPosY < $posY) {
                $textPosY = $posY;
            }

            $this->TCPDF->SetXY($posX, $textPosY);
            $this->TCPDF->MultiCell(
                $columnWidth,
                $this->cellHeight,
                $columnName,
                0,
                'L',
                false,
                0,
                '',
                '',
                true,
                0,
                false,
                true,
                0,
                'T'
            );
            $this->TCPDF->SetXY($posX + $columnWidth, $posY);

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
