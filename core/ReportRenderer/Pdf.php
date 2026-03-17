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

    public const MAX_ROW_COUNT = 28;
    public const TABLE_HEADER_ROW_COUNT = 6;
    public const NO_DATA_ROW_COUNT = 6;
    public const MAX_GRAPH_REPORTS = 3;
    public const MAX_2COL_TABLE_REPORTS = 2;

    public const IMPORT_FONT_PATH = 'plugins/ImageGraph/fonts/unifont.ttf';
    public const PDF_CONTENT_TYPE = 'pdf';
    public const PORTRAIT = 'P';

    private $reportFontStyle = '';
    private $reportSimpleFontSize = 8.5;
    private $reportHeaderFontSize = 16;
    private $cellHeight = 6;
    private $bottomMargin = 17;
    private $reportWidthPortrait = 195;
    private $minWidthLabelCellPortrait = 80;
    private $minWidthLabelCellPortraitShort = 35;
    private $logoWidth = 16;
    private $logoHeight = 16;
    private $totalWidth;
    private $cellWidth;
    private $labelCellWidth;
    private $maxRowHeight = 13;
    private $maxLabelCharacter = 135;
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
    private $labelShortContentThreshold = 100;
    private $columnCellWidths = array();
    private $labelThirdLinePadding = 4;
    private $expandedMetricRightPadding = 1.0;
    private $twoColumnLabelRatio = 0.7;
    private $threeColumnLabelRatio = 0.65;
    private $fourColumnLabelRatio = 0.6;
    private $numColumnsBeforeShrink = 8;
    private $tableWidthCache = array();
    private $hasFrontPageBreak = false;

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

        $this->TCPDF->AddPage(self::PORTRAIT);
        $this->hasFrontPageBreak = true;
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

        $usedFrontPageBreak = false;
        if ($this->hasFrontPageBreak && $this->currentPage === 0) {
            $this->currentPage++;
            $this->TCPDF->setPageOrientation(self::PORTRAIT, '', $this->bottomMargin);
            $this->hasFrontPageBreak = false;
            $usedFrontPageBreak = true;
        }

        // Only a page break before if the current report has some data
        if (
            !$usedFrontPageBreak
            && $reportHasData
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

            // Scheduled reports should never switch to landscape layouts to keep a consistent portrait output
            $this->TCPDF->setPageOrientation(self::PORTRAIT, '', $this->bottomMargin);
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

    private function formatText(?string $text): string
    {
        return Common::unsanitizeInputValue($text);
    }

    private function limitTextLength(string $text, int $maxLength): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        return mb_substr($text, 0, $maxLength - 1) . '…';
    }

    private function paintReportTable(): void
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
            $url = false;
            $rowMetadata = isset($rowsMetadata[$rowId]) ? $rowsMetadata[$rowId]->getColumns() : array();
            if (isset($rowMetadata['url'])) {
                $url = $rowMetadata['url'];
            }
            $metricsPaddingApplied = false;
            $previousCellPadding = null;
            $labelState = $this->computeLabelRenderState($rowMetrics, $rowMetadata, $leftSpacesBeforeLogo, $url);
            $labelText = $labelState['text'];
            $rowHeight = $labelState['rowHeight'];
            $maxHeight = $labelState['maxHeight'];
            $verticalAlign = $labelState['verticalAlign'];
            $shouldIncreaseLineHeight = $labelState['shouldIncreaseLineHeight'];
            $isLogoDisplayable = $labelState['isLogoDisplayable'];
            if ((float)$this->TCPDF->GetY() + $rowHeight > $this->TCPDF->getPageHeight() - $this->TCPDF->getBreakMargin()) {
                $this->TCPDF->AddPage();
                $this->paintReportTableHeader();
            }
            foreach ($this->reportColumns as $columnId => $columnName) {
                // Label column
                if ($columnId == 'label') {
                    $text = $labelText;
                    $posX = $this->TCPDF->GetX();
                    $posY = $this->TCPDF->GetY();
                    list($previousCellHeightRatio, $previousCellPaddingForLabel) = $this->applyLabelCellStyle($shouldIncreaseLineHeight);
                    $this->TCPDF->MultiCell(
                        $this->labelCellWidth,
                        $rowHeight,
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
                        $maxHeight,
                        $verticalAlign
                    );
                    $this->restoreLabelCellStyle($shouldIncreaseLineHeight, $previousCellHeightRatio, $previousCellPaddingForLabel);
                    $this->renderLabelLinkAndLogo($url, $posX, $posY, $rowHeight, $rowMetadata, $isLogoDisplayable, $logoWidth, $logoHeight);
                } else {
                    // metrics column

                    // No value means 0
                    if (empty($rowMetrics[$columnId])) {
                        $rowMetrics[$columnId] = 0;
                    }
                    $columnWidth = $this->getColumnWidth($columnId);
                    if (!$metricsPaddingApplied) {
                        $previousCellPadding = $this->applyCellPaddingOffset(1, 1);
                        $metricsPaddingApplied = true;
                    }
                    $this->TCPDF->Cell(
                        $columnWidth,
                        $rowHeight,
                        NumberFormatter::getInstance()->format($rowMetrics[$columnId]),
                        'LR',
                        0,
                        'L',
                        $fill,
                        '',
                        0,
                        false,
                        'T',
                        'T'
                    );
                }
            }
            if ($metricsPaddingApplied) {
                $this->restoreCellPaddingOffset($previousCellPadding);
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

    private function getExtraLineHeight(int $labelLineCount): float
    {
        $ratioDelta = $labelLineCount === 2 ? 0.1 : 0.3;
        return $this->cellHeight * $ratioDelta * ($labelLineCount - 1);
    }

    private function computeLabelRenderState(array $rowMetrics, array $rowMetadata, string $leftSpacesBeforeLogo, &$url): array
    {
        $isLogoDisplayable = isset($rowMetadata['logo']);
        $labelText = '';
        if (isset($rowMetrics['label'])) {
            $labelText = trim($rowMetrics['label']);
            $urlString = $this->isUrl($labelText);
            if (!$url && $urlString !== false) {
                $url = $urlString;
            }
            $labelText = $this->limitTextLength($labelText, $this->maxLabelCharacter);
            if ($isLogoDisplayable) {
                $labelText = $leftSpacesBeforeLogo . $labelText;
            }
        }
        $labelText = $this->formatText($labelText);
        $labelLineCount = $this->TCPDF->getNumLines($labelText, $this->labelCellWidth);
        $shouldIncreaseLineHeight = $isLogoDisplayable && $labelLineCount > 1;

        $previousCellHeightRatio = null;
        if ($shouldIncreaseLineHeight) {
            $previousCellHeightRatio = $this->TCPDF->getCellHeightRatio();
            $this->TCPDF->setCellHeightRatio($previousCellHeightRatio + 0.3);
        }
        $rowHeight = $this->getLabelRowHeight($labelText);
        if ($shouldIncreaseLineHeight) {
            $rowHeight += $this->getExtraLineHeight($labelLineCount);
            $this->TCPDF->setCellHeightRatio($previousCellHeightRatio);
        }

        $maxHeight = $shouldIncreaseLineHeight
            ? $rowHeight
            : $this->getLabelRowMaxHeight($rowHeight);
        $verticalAlign = $shouldIncreaseLineHeight ? 'T' : 'M';

        return array(
            'text' => $labelText,
            'rowHeight' => $rowHeight,
            'maxHeight' => $maxHeight,
            'verticalAlign' => $verticalAlign,
            'shouldIncreaseLineHeight' => $shouldIncreaseLineHeight,
            'isLogoDisplayable' => $isLogoDisplayable,
        );
    }

    private function applyLabelCellStyle(bool $shouldIncreaseLineHeight): array
    {
        if (!$shouldIncreaseLineHeight) {
            return array(null, null);
        }

        $previousCellHeightRatio = $this->TCPDF->getCellHeightRatio();
        $this->TCPDF->setCellHeightRatio($previousCellHeightRatio + 0.3);
        $previousCellPaddingForLabel = $this->applyCellPaddingOffset();

        return array($previousCellHeightRatio, $previousCellPaddingForLabel);
    }

    private function restoreLabelCellStyle(
        bool $shouldIncreaseLineHeight,
        ?float $previousCellHeightRatio,
        ?array $previousCellPaddingForLabel
    ): void {
        if (!$shouldIncreaseLineHeight || $previousCellHeightRatio === null || $previousCellPaddingForLabel === null) {
            return;
        }

        $this->TCPDF->setCellHeightRatio($previousCellHeightRatio);
        $this->restoreCellPaddingOffset($previousCellPaddingForLabel);
    }

    private function applyCellPaddingOffset(float $left = 0, float $top = 0.8): array
    {
        $previousCellPadding = $this->TCPDF->getCellPaddings();
        $this->TCPDF->setCellPaddings(
            $previousCellPadding['L'] + $left,
            $previousCellPadding['T'] + $top,
            $previousCellPadding['R'],
            $previousCellPadding['B']
        );
        return $previousCellPadding;
    }

    private function restoreCellPaddingOffset(array $previousCellPaddings): void
    {
        $this->TCPDF->setCellPaddings(
            $previousCellPaddings['L'],
            $previousCellPaddings['T'],
            $previousCellPaddings['R'],
            $previousCellPaddings['B']
        );
    }

    /**
     * Gets the row height for a label. This will be the total height including wrapping
     * but still having a maximum height
     */
    private function getLabelRowHeight(string $text): float
    {
        $maxHeight = $this->maxRowHeight;
        $labelHeight = $this->TCPDF->getStringHeight($this->labelCellWidth, $text);
        $labelHeight = ceil($labelHeight * 2) / 2; // round up to nearest 0.5 for stable row heights

        if ($labelHeight > $maxHeight) {
            return $maxHeight;
        }

        if ($labelHeight < $this->cellHeight) {
            return $this->cellHeight;
        }

        return $labelHeight + 1;
    }

    /**
     * @param false|string $url
     * @param array<string, mixed> $rowMetadata
     */
    private function renderLabelLinkAndLogo(
        $url,
        float $posX,
        float $posY,
        float $rowHeight,
        array $rowMetadata,
        bool $isLogoDisplayable,
        float &$logoWidth,
        float &$logoHeight
    ): void {
        if ($url) {
            $this->TCPDF->Link($posX, $posY, $this->labelCellWidth, $rowHeight, $url);
        }
        $this->TCPDF->SetXY($posX + $this->labelCellWidth, $posY);

        if (!$isLogoDisplayable) {
            return;
        }

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

    /**
     * Checks if a string might be a url or not
     * Will return the string with an 'https' protocol if it is a valid url
     * @return false|string
     */
    private function isUrl(string $value)
    {
        $candidate = $value;
        if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $candidate)) {
            $candidate = 'https://' . $candidate;
        }
        $host = parse_url($candidate, PHP_URL_HOST);
        $isValidHost = $host && strpos($host, '.') !== false;
        $isValidUrl = filter_var($candidate, FILTER_VALIDATE_URL) !== false && $isValidHost;
        return $isValidUrl ? $candidate : false;
    }

    /**
     * This is only useful when label row is 3 lines,
     * we needed to make max row bigger so that it can be centered vertically better
     */
    private function getLabelRowMaxHeight(float $rowHeight): float
    {
        if ($rowHeight >= $this->maxRowHeight) {
            return $rowHeight + $this->labelThirdLinePadding;
        }
        return $rowHeight;
    }

    /**
     * Sets initial label width based on column count and content heuristics.
     */
    private function setInitialLabelWidth(int $columnsCount): void
    {
        if ($columnsCount <= 1) {
            $this->labelCellWidth = $this->totalWidth;
            $this->cellWidth = 0;
            return;
        }

        if ($columnsCount < 5) {
            if ($columnsCount === 2) {
                $labelRatio = $this->twoColumnLabelRatio;
            } elseif ($columnsCount === 3) {
                $labelRatio = $this->threeColumnLabelRatio;
            } else {
                $labelRatio = $this->fourColumnLabelRatio;
            }

            $metricColumns = $columnsCount - 1;
            $this->labelCellWidth = max((int) round($this->totalWidth * $labelRatio), $this->minWidthLabelCellPortrait);
            $this->cellWidth = (int) round(($this->totalWidth - $this->labelCellWidth) / $metricColumns);
            $this->totalWidth = $this->labelCellWidth + $metricColumns * $this->cellWidth;
            return;
        }

        if (!$this->reportHasData() || !$this->shouldUseShortLabelWidth()) {
            return;
        }

        $this->labelCellWidth = $this->minWidthLabelCellPortraitShort;
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / ($columnsCount - 1));
        $this->totalWidth = $this->labelCellWidth + ($columnsCount - 1) * $this->cellWidth;
    }

    private function shrinkLabelWidthForSingleLineLabels(int $columnsCount): void
    {
        if ($columnsCount <= $this->numColumnsBeforeShrink || !$this->reportHasData()) {
            return;
        }

        $maxLabelWidth = $this->getMaxSingleLineLabelWidth();
        if ($maxLabelWidth === null) {
            return;
        }
        $maxLabelWidth = max($maxLabelWidth, $this->minWidthLabelCellPortraitShort);
        if ($maxLabelWidth >= $this->labelCellWidth) {
            return;
        }

        $metricColumns = $columnsCount - 1;
        $this->labelCellWidth = $maxLabelWidth;
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / $metricColumns);
        $this->totalWidth = $this->labelCellWidth + $metricColumns * $this->cellWidth;
    }

    private function capLabelWidthForManyColumns(int $columnsCount): void
    {
        if ($columnsCount <= $this->numColumnsBeforeShrink) {
            return;
        }

        $maxLabelWidth = round($this->totalWidth * 0.35, 2);
        if ($this->labelCellWidth <= $maxLabelWidth) {
            return;
        }

        $metricColumns = $columnsCount - 1;
        $this->labelCellWidth = $maxLabelWidth;
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / $metricColumns);
        $this->totalWidth = $this->labelCellWidth + $metricColumns * $this->cellWidth;
    }

    private function getMaxSingleLineLabelWidth(): ?float
    {
        if (
            !empty($this->tableWidthCache['maxSingleLineLabelWidthFor'])
            && (float) $this->tableWidthCache['maxSingleLineLabelWidthFor'] === (float) $this->labelCellWidth
        ) {
            return $this->tableWidthCache['maxSingleLineLabelWidth'];
        }

        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);

        $rowsMetadata = array();
        if (!empty($this->reportRowsMetadata)) {
            $rowsMetadata = $this->reportRowsMetadata->getRows();
        }

        $maxWidth = 0.0;

        foreach ($this->report->getRows() as $rowId => $row) {
            $label = $row->getColumn('label');
            if ($label === false || $label === null) {
                continue;
            }

            $labelText = $this->buildFormattedLabelTextForRow(
                $rowId,
                (string) $label,
                $rowsMetadata,
                $this->maxLabelCharacter
            );
            if ($this->TCPDF->getNumLines($labelText, $this->labelCellWidth) > 1) {
                return null;
            }

            $width = $this->TCPDF->GetStringWidth($labelText);
            if ($width > $maxWidth) {
                $maxWidth = $width;
            }
        }

        if ($maxWidth <= 0) {
            $this->tableWidthCache['maxSingleLineLabelWidth'] = null;
            $this->tableWidthCache['maxSingleLineLabelWidthFor'] = $this->labelCellWidth;
            return null;
        }

        $padding = $this->TCPDF->getCellPaddings();
        $maxWidth = $maxWidth + $padding['L'] + $padding['R'];
        $this->tableWidthCache['maxSingleLineLabelWidth'] = $maxWidth;
        $this->tableWidthCache['maxSingleLineLabelWidthFor'] = $this->labelCellWidth;
        return $maxWidth;
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

    /**
     * This function will try to show all values for selected metric columns.
     * Will adjust other column widths to accommodate this
     */
    private function adjustMetricColumnWidthsToContent(): void
    {
        if (!$this->reportHasData()) {
            return;
        }

        $metricColumnsToAdjust = array('revenue', 'ecommerce_revenue', 'avg_time_on_site');
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);

        foreach ($metricColumnsToAdjust as $columnId) {
            $this->adjustMetricColumnWidthToContent($columnId);
        }
    }

    /**
     * This function will try to adjust column width based on the content.
     * This will try to make other columns smaller to accommodate this
     */
    private function adjustMetricColumnWidthToContent(string $columnId): void
    {
        if (!array_key_exists($columnId, $this->reportColumns) || !isset($this->columnCellWidths[$columnId])) {
            return;
        }

        $requiredWidth = $this->getMaxFormattedColumnWidth($columnId);
        if ($requiredWidth <= 0) {
            return;
        }

        $currentWidth = $this->columnCellWidths[$columnId];
        if ($requiredWidth <= $currentWidth) {
            return;
        }

        $additionalWidth = $requiredWidth - $currentWidth;
        if ($additionalWidth <= 1.0) {
            $requiredWidth += $this->expandedMetricRightPadding;
            $additionalWidth = $requiredWidth - $currentWidth;
        }
        $remainingWidthToGain = $additionalWidth;
        $minMetricWidth = 10;

        $adjustableColumns = array();
        foreach ($this->columnCellWidths as $otherColumnId => $width) {
            if ($otherColumnId === 'label' || $otherColumnId === $columnId) {
                continue;
            }
            if ($width <= $minMetricWidth) {
                continue;
            }
            $adjustableColumns[$otherColumnId] = $width;
        }

        while ($remainingWidthToGain > 0 && !empty($adjustableColumns)) {
            $share = $remainingWidthToGain / count($adjustableColumns);
            $updatedColumns = array();

            foreach ($adjustableColumns as $otherColumnId => $availableWidth) {
                $maxReducible = $availableWidth - $minMetricWidth;
                if ($maxReducible <= 0) {
                    continue;
                }
                $reduction = min($maxReducible, $share);
                if ($reduction <= 0) {
                    continue;
                }
                $this->columnCellWidths[$otherColumnId] -= $reduction;
                $remainingWidthToGain -= $reduction;

                $newWidth = $availableWidth - $reduction;
                if ($newWidth > $minMetricWidth && $remainingWidthToGain > 0) {
                    $updatedColumns[$otherColumnId] = $newWidth;
                }

                if ($remainingWidthToGain <= 0) {
                    break 2;
                }
            }

            $adjustableColumns = $updatedColumns;
        }

        $appliedWidth = $additionalWidth - $remainingWidthToGain;
        if ($appliedWidth <= 0) {
            return;
        }

        $this->columnCellWidths[$columnId] += $appliedWidth;
    }

    /**
     * Computes maximum column width for a given metric column
     */
    private function getMaxFormattedColumnWidth(string $columnId): float
    {
        if (
            !empty($this->tableWidthCache)
            && isset($this->tableWidthCache['metricMaxWidths'][$columnId])
        ) {
            $maxWidth = $this->tableWidthCache['metricMaxWidths'][$columnId];
            if ($maxWidth <= 0) {
                return 0;
            }
            return $maxWidth + 2;
        }

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

    private function initializeTableWidthCache(): void
    {
        $this->tableWidthCache = array(
            'ready' => true,
            'labelTooLong' => false,
            'labelFitsShortWidth' => true,
            'maxLabelLength' => 0,
            'metricMaxWidths' => array(),
            'maxSingleLineLabelWidth' => null,
            'maxSingleLineLabelWidthFor' => null,
        );

        if (!$this->reportHasData()) {
            return;
        }

        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);

        $maxCharacters = $this->maxLabelCharacter;
        $rowsMetadata = array();
        if (!empty($this->reportRowsMetadata)) {
            $rowsMetadata = $this->reportRowsMetadata->getRows();
        }

        $metricColumnsToAdjust = array('revenue', 'ecommerce_revenue', 'avg_time_on_site');
        foreach ($metricColumnsToAdjust as $columnId) {
            $this->tableWidthCache['metricMaxWidths'][$columnId] = 0.0;
        }

        foreach ($this->report->getRows() as $rowId => $row) {
            $label = $row->getColumn('label');
            if ($label !== false && $label !== null) {
                $rawLabel = (string) $label;
                $length = mb_strlen($rawLabel);
                if ($length > $this->tableWidthCache['maxLabelLength']) {
                    $this->tableWidthCache['maxLabelLength'] = $length;
                }
                if ($length > $maxCharacters) {
                    $this->tableWidthCache['labelTooLong'] = true;
                }
                if ($this->tableWidthCache['labelFitsShortWidth']) {
                    $formattedLabel = $this->buildFormattedLabelTextForRow(
                        $rowId,
                        $rawLabel,
                        $rowsMetadata,
                        $maxCharacters
                    );
                    if ($this->TCPDF->getNumLines($formattedLabel, $this->minWidthLabelCellPortraitShort) > 1) {
                        $this->tableWidthCache['labelFitsShortWidth'] = false;
                    }
                }
            }

            foreach ($metricColumnsToAdjust as $columnId) {
                if (!array_key_exists($columnId, $this->reportColumns)) {
                    continue;
                }
                $value = $row->getColumn($columnId);
                if ($value === false || $value === null) {
                    continue;
                }
                $formattedValue = NumberFormatter::getInstance()->format($value);
                $width = $this->TCPDF->GetStringWidth($formattedValue);
                if ($width > $this->tableWidthCache['metricMaxWidths'][$columnId]) {
                    $this->tableWidthCache['metricMaxWidths'][$columnId] = $width;
                }
            }
        }
    }


    /**
     * Will check if label column could use a shorter width.
     * This is done so that we can fit more metrics in the same row for data table with no label that is too long
     */
    private function shouldUseShortLabelWidth(): bool
    {
        if (empty($this->tableWidthCache) || empty($this->tableWidthCache['ready'])) {
            $this->initializeTableWidthCache();
        }

        if (empty($this->tableWidthCache['maxLabelLength'])) {
            return false;
        }

        if (!empty($this->tableWidthCache['labelTooLong'])) {
            return false;
        }

        if ($this->tableWidthCache['maxLabelLength'] >= $this->labelShortContentThreshold) {
            return false;
        }

        if (empty($this->tableWidthCache['labelFitsShortWidth'])) {
            return false;
        }

        return $this->tableWidthCache['maxLabelLength'] > 0;
    }

    private function buildFormattedLabelTextForRow(
        int $rowId,
        string $label,
        array $rowsMetadata,
        int $maxCharacters
    ): string {
        $labelText = trim($label);
        $labelText = $this->limitTextLength($labelText, $maxCharacters);

        if (isset($rowsMetadata[$rowId])) {
            $rowMeta = $rowsMetadata[$rowId]->getColumns();
            if (isset($rowMeta['logo'])) {
                $labelText = str_repeat(' ', $this->leftSpacesBeforeLogo) . $labelText;
            }
        }

        return $this->formatText($labelText);
    }

    private function paintGraph(): void
    {
        $imageGraph = parent::getStaticGraph(
            $this->reportMetadata,
            self::IMAGE_GRAPH_WIDTH_PORTRAIT,
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
    private function paintReportTableHeader(): void
    {
        $initPosX = 10;

        $this->initializeTableColumnWidths();
        $this->setupHeaderRenderingStyle();

        $posY = $this->TCPDF->GetY();
        list($columnData, $maxCellHeight) = $this->buildHeaderColumnData();

        $this->TCPDF->SetXY($initPosX, $posY);
        $this->renderHeaderColumns($columnData, $initPosX, $posY, $maxCellHeight);

        $extraSpacing = 1;
        $this->TCPDF->Ln($extraSpacing);
        $this->TCPDF->SetXY($initPosX, $posY + $maxCellHeight + $extraSpacing);

        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
    }

    /**
     * Will initialize table column widths,
     * this will include adjusting label and revenue columns
     */
    private function initializeTableColumnWidths(): void
    {
        $columnsCount = count($this->reportColumns);
        if ($columnsCount === 0) {
            return;
        }

        $this->totalWidth = $this->reportWidthPortrait;
        $minLabelWidth = $this->minWidthLabelCellPortrait;
        $this->labelCellWidth = max(round($this->totalWidth / $columnsCount), $minLabelWidth);

        $metricColumns = max(1, $columnsCount - 1);
        $this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / $metricColumns);
        $this->totalWidth = $this->labelCellWidth + $metricColumns * $this->cellWidth;

        $this->initializeTableWidthCache();
        $this->setInitialLabelWidth($columnsCount);
        $this->capLabelWidthForManyColumns($columnsCount);
        $this->shrinkLabelWidthForSingleLineLabels($columnsCount);

        $this->columnCellWidths = array();
        foreach ($this->reportColumns as $columnId => $_) {
            $this->columnCellWidths[$columnId] = $columnId === 'label' ? $this->labelCellWidth : $this->cellWidth;
        }

        $this->adjustMetricColumnWidthsToContent();
        $this->totalWidth = array_sum($this->columnCellWidths);
    }

    private function setupHeaderRenderingStyle(): void
    {
        $this->TCPDF->SetFillColor(
            $this->tableHeaderBackgroundColor[0],
            $this->tableHeaderBackgroundColor[1],
            $this->tableHeaderBackgroundColor[2]
        );
        $this->TCPDF->SetTextColor(
            $this->tableHeaderTextColor[0],
            $this->tableHeaderTextColor[1],
            $this->tableHeaderTextColor[2]
        );
        $this->TCPDF->SetLineWidth(.3);
        $this->setBorderColor();
        $this->TCPDF->SetFont($this->reportFont, 'B');
        $this->TCPDF->SetFillColor(255);
        $this->TCPDF->SetTextColor(
            $this->tableHeaderBackgroundColor[0],
            $this->tableHeaderBackgroundColor[1],
            $this->tableHeaderBackgroundColor[2]
        );
        $this->TCPDF->SetDrawColor(255);
    }

    /**
     * Will adjust table headers based on their column name and make them be closer to the table
     * @return array
     */
    private function buildHeaderColumnData(): array
    {
        $columnData = array();
        $maxCellHeight = $this->cellHeight;

        foreach ($this->reportColumns as $columnId => $columnName) {
            $columnName = $this->formatText($columnName);
            $columnWidth = $this->getColumnWidth($columnId);
            $textHeight = $this->TCPDF->getStringHeight($columnWidth, $columnName);
            $cellHeight = max($textHeight, $this->cellHeight);

            $columnData[] = array(
                'text' => $columnName,
                'width' => $columnWidth,
                'height' => $cellHeight,
                'textHeight' => $textHeight,
            );

            if ($cellHeight > $maxCellHeight) {
                $maxCellHeight = $cellHeight;
            }
        }

        return array($columnData, $maxCellHeight);
    }

    private function renderHeaderColumns(array $columnData, float $initPosX, float $posY, float $maxCellHeight): void
    {
        $this->TCPDF->SetFillColor(
            $this->tableHeaderBackgroundColor[0],
            $this->tableHeaderBackgroundColor[1],
            $this->tableHeaderBackgroundColor[2]
        );
        $this->TCPDF->SetTextColor(
            $this->tableHeaderTextColor[0],
            $this->tableHeaderTextColor[1],
            $this->tableHeaderTextColor[2]
        );
        $this->TCPDF->SetDrawColor(
            $this->tableCellBorderColor[0],
            $this->tableCellBorderColor[1],
            $this->tableCellBorderColor[2]
        );

        $posX = $initPosX;
        foreach ($columnData as $columnInfo) {
            $columnWidth = $columnInfo['width'];
            $textHeight = $columnInfo['textHeight'];

            $this->TCPDF->Rect($posX, $posY, $columnWidth, $maxCellHeight, 'F');

            $textPosY = $this->calculateHeaderTextY($posY, $maxCellHeight, $textHeight);
            $this->TCPDF->SetXY($posX, $textPosY);
            $this->TCPDF->MultiCell(
                $columnWidth,
                $this->cellHeight,
                $columnInfo['text'],
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
            $posX = $this->TCPDF->GetX();
        }
    }

    private function calculateHeaderTextY(float $posY, float $maxCellHeight, float $textHeight): float
    {
        if ($textHeight <= 0) {
            $textHeight = $this->cellHeight;
        }

        $bottomPadding = $this->headerBottomPadding;
        if ($textHeight <= $this->cellHeight) {
            $bottomPadding = 0;
        } elseif ($textHeight <= $this->cellHeight * 2) {
            $bottomPadding = $this->headerBottomPaddingShort;
        }

        $availableSpace = max(0, $maxCellHeight - $textHeight);
        $textPosY = $posY + $availableSpace - $bottomPadding;

        $minY = $posY;
        $maxY = $posY + $maxCellHeight - $textHeight;
        if ($textPosY < $minY) {
            return $minY;
        }
        if ($textPosY > $maxY) {
            return $maxY;
        }

        return $textPosY;
    }

    /**
     * Prints a message
     *
     * @param string $message
     */
    private function paintMessage($message): void
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
    public function getAttachments($report, $processedReports, $prettyDate): array
    {
        return array();
    }
}
