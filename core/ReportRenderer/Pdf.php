<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik_ReportRenderer
 */

/**
 * @see libs/tcpdf
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/PDFReports/config/tcpdf_config.php';
require_once PIWIK_INCLUDE_PATH . '/libs/tcpdf/config/lang/eng.php';
require_once PIWIK_INCLUDE_PATH . '/core/TCPDF.php';

/**
 *
 * @package Piwik_ReportRenderer
 */
class Piwik_ReportRenderer_Pdf extends Piwik_ReportRenderer
{
    private $reportFontStyle = '';
    private $reportSimpleFontSize = 9;
    private $reportHeaderFontSize = 22;
    private $cellHeight = 6;
    private $bottomMargin = 20;
    private $reportWidthPortrait = 180;
    private $reportWidthLandscape = 270;
    private $minWidthLabelCell = 100;
    private $maxColumnCountPortraitOrientation = 6;
    private $logoWidth = 16;
    private $logoHeight = 16;
    private $totalWidth;
    private $cellWidth;
    private $labelCellWidth;
    private $truncateAfter = 50;
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
    private $reportColumns;
    private $reportRowsMetadata;
    private $currentPage = 0;
    private $lastTableIsSimpleReport = false;
    private $reportFont = Piwik_ReportRenderer::DEFAULT_REPORT_FONT;
    private $TCPDF;

    public function __construct()
    {
        $this->TCPDF = new Piwik_TCPDF();
        $this->headerTextColor = preg_split("/,/", Piwik_ReportRenderer::REPORT_TITLE_TEXT_COLOR);
        $this->reportTextColor = preg_split("/,/", Piwik_ReportRenderer::REPORT_TEXT_COLOR);
        $this->tableHeaderBackgroundColor = preg_split("/,/", Piwik_ReportRenderer::TABLE_HEADER_BG_COLOR);
        $this->tableHeaderTextColor = preg_split("/,/", Piwik_ReportRenderer::TABLE_HEADER_TEXT_COLOR);
        $this->tableCellBorderColor = preg_split("/,/", Piwik_ReportRenderer::TABLE_CELL_BORDER_COLOR);
        $this->tableBackgroundColor = preg_split("/,/", Piwik_ReportRenderer::TABLE_BG_COLOR);
    }

    public function setLocale($locale)
    {
        switch ($locale)
        {
            case 'zh-tw':
            case 'ja':
                $reportFont = 'msungstdlight';
                break;

            case 'zh-cn':
                $reportFont = 'stsongstdlight';
                break;

            case 'ko':
                $reportFont = 'hysmyeongjostdmedium';
                break;

            case 'ar':
                $reportFont = 'almohanad';
                break;

            case 'en':
            default:
                $reportFont = Piwik_ReportRenderer::DEFAULT_REPORT_FONT;
                break;
        }
        $this->reportFont = $reportFont;
    }

    public function setReportBasics($websiteName, $prettyDate, $description, $reportMetadata)
    {
        $websiteTitle = $this->formatText(Piwik_Translate('General_Website') . " " . $websiteName);
        $dateRange = $this->formatText(Piwik_Translate('General_DateRange') . " " . $prettyDate);

        //Setup Footer font and data
        $this->TCPDF->SetFooterFont(array($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize));
        $this->TCPDF->SetFooterContent($websiteTitle . " | " . $dateRange . " | ");
    }

    public function sendToDisk($filename)
    {
        $filename = Piwik_ReportRenderer::appendExtension($filename, "pdf");
        $outputFilename = Piwik_ReportRenderer::getOutputPath($filename);

        $this->TCPDF->Output($outputFilename, 'F');

        return $outputFilename;
    }

    public function sendToBrowserDownload($filename)
    {
        $filename = Piwik_ReportRenderer::appendExtension($filename, "pdf");
        $this->TCPDF->Output($filename, 'D');
    }

    public function renderFrontPage($websiteName, $prettyDate, $description, $reportMetadata)
    {
        $this->TCPDF->setPrintHeader(false);
        //    	$this->SetMargins($left = , $top, $right=-1, $keepmargins=true)
        $this->TCPDF->AddPage('P');
        $this->TCPDF->AddFont($this->reportFont, '', '', false);
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportSimpleFontSize);
        //Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false) {
        $this->TCPDF->Bookmark(Piwik_Translate('PDFReports_FrontPage'));
        $this->TCPDF->Image(Piwik::getLogoPath(), $this->logoImagePosition[0], $this->logoImagePosition[1], 180 / $factor = 2, 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300);
        $this->TCPDF->Ln(8);

        $websiteTitle = $this->formatText(Piwik_Translate('General_Website') . " " . $websiteName);
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize + 5);
        $this->TCPDF->SetTextColor($this->headerTextColor[0], $this->headerTextColor[1], $this->headerTextColor[2]);
        $this->TCPDF->Cell(40, 210, $websiteTitle);
        $this->TCPDF->Ln(8 * 4);

        $dateRange = $this->formatText(Piwik_Translate('General_DateRange') . " " . $prettyDate);
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
        $this->TCPDF->Cell(40, 210, $dateRange);
        $this->TCPDF->Ln(8 * 20);
        $this->TCPDF->Write(1, $this->formatText($description));
        $this->TCPDF->Ln(8);
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportHeaderFontSize);
        $this->TCPDF->Ln();
    }

    /**
     * Generate a header of page.
     */
    private function paintReportHeader()
    {
        $currentTableIsSimpleReport = count($this->reportColumns) == 2;
        $reportHasData = count($this->report) > 0;
        // Only a page break before if the current report has some data
        if (($reportHasData
             // or if it is the first report
             || $this->currentPage == 0)
            && !($this->lastTableIsSimpleReport
                 && $currentTableIsSimpleReport)) {
            $this->currentPage++;
            $columnCount = count($this->reportColumns);
            $this->TCPDF->AddPage();
            // Pages without data are always Portrait
            if ($reportHasData) {
                $this->TCPDF->setPageOrientation($columnCount > $this->maxColumnCountPortraitOrientation ? 'L' : 'P', '', $this->bottomMargin);
            }
        }
        $this->lastTableIsSimpleReport = $currentTableIsSimpleReport;
        $title = $this->formatText($this->reportMetadata['name']);
        $this->TCPDF->SetFont($this->reportFont, $this->reportFontStyle, $this->reportHeaderFontSize);
        $this->TCPDF->SetTextColor($this->headerTextColor[0], $this->headerTextColor[1], $this->headerTextColor[2]);
        $this->TCPDF->Bookmark($title);
        $this->TCPDF->Cell(40, 20, $title);
        $this->TCPDF->Ln();
        $this->TCPDF->SetFont($this->reportFont, '', $this->reportSimpleFontSize);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
    }

    private function setBorderColor()
    {
        $this->TCPDF->SetDrawColor($this->tableCellBorderColor[0], $this->tableCellBorderColor[1], $this->tableCellBorderColor[2]);
    }

    public function renderReport($processedReport)
    {
        $this->reportMetadata = $processedReport['metadata'];
        $this->report = $processedReport['reportData'];
        $this->reportColumns = $processedReport['columns'];
        $this->reportRowsMetadata = $processedReport['reportMetadata'];

        $this->paintReportHeader();
        if (empty($this->report)) {
            $this->paintMessage(Piwik_Translate('CoreHome_ThereIsNoDataForThisReport'));
            return;
        }
        $this->paintReportTableHeader();
        $this->paintReportTable();
    }

    private function formatText($text)
    {
        return Piwik_Common::unsanitizeInputValue($text);
    }

    private function paintReportTable()
    {
        //Color and font restoration
        $this->TCPDF->SetFillColor($this->tableBackgroundColor[0], $this->tableBackgroundColor[1], $this->tableBackgroundColor[2]);
        $this->TCPDF->SetTextColor($this->reportTextColor[0], $this->reportTextColor[1], $this->reportTextColor[2]);
        $this->TCPDF->SetFont('');

        $fill = false;
        $logo = $url = false;
        $posY = $posX = 0;
        $leftSpacesBeforeLogo = str_repeat(' ', $this->leftSpacesBeforeLogo);

        $logoWidth = $this->logoWidth;
        $logoHeight = $this->logoHeight;

        // Draw a body of report table
        foreach ($this->report as $rowId => $row)
        {
            if (isset($this->reportRowsMetadata[$rowId]['url'])) {
                $url = $this->reportRowsMetadata[$rowId]['url'];
            }
            foreach ($this->reportColumns as $columnId => $columnName)
            {
                // Label column
                if ($columnId == 'label') {
                    $isLogoDisplayable = isset($this->reportRowsMetadata[$rowId]['logo']);
                    $text = '';
                    $posX = $this->TCPDF->GetX();
                    $posY = $this->TCPDF->GetY();
                    if (isset($row[$columnId])) {
                        $text = substr($row[$columnId], 0, $this->truncateAfter);
                        if ($isLogoDisplayable) {
                            $text = $leftSpacesBeforeLogo . $text;
                        }
                    }
                    $text = $this->formatText($text);

                    $this->TCPDF->Cell($this->labelCellWidth, $this->cellHeight, $text, 'LR', 0, 'L', $fill, $url);

                    if ($isLogoDisplayable) {
                        if (isset($this->reportRowsMetadata[$rowId]['logoWidth'])) {
                            $logoWidth = $this->reportRowsMetadata[$rowId]['logoWidth'];
                        }
                        if (isset($this->reportRowsMetadata[$rowId]['logoHeight'])) {
                            $logoHeight = $this->reportRowsMetadata[$rowId]['logoHeight'];
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
                        $this->TCPDF->Image(Piwik_Common::getPathToPiwikRoot() . "/" . $this->reportRowsMetadata[$rowId]['logo'], $posX + ($leftMargin = 2), $posY + $topMargin, $logoWidth / 4);
                        $this->TCPDF->SetXY($restoreX, $restoreY);
                    }
                }
                    // metrics column
                else
                {
                    // No value means 0
                    if (empty($row[$columnId])) {
                        $row[$columnId] = 0;
                    }
                    $this->TCPDF->Cell($this->cellWidth, $this->cellHeight, $row[$columnId], 'LR', 0, 'L', $fill);
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

    /**
     * Draw the table header (first row)
     */
    private function paintReportTableHeader()
    {
        $initPosX = 10;

        // Get the longest column name
        $longestColumnName = '';
        foreach ($this->reportColumns as $columnName)
        {
            if (strlen($columnName) > strlen($longestColumnName)) {
                $longestColumnName = $columnName;
            }
        }

        // Decide on page orientation
        $columnsCount = count($this->reportColumns);
        if ($columnsCount <= 2) {
            $totalWidth = $this->reportWidthPortrait * 2 / 3;
        }
        else if ($columnsCount > $this->maxColumnCountPortraitOrientation) {
            $totalWidth = $this->reportWidthLandscape;
        }
        else
        {
            $totalWidth = $this->reportWidthPortrait;
        }
        // Computes available column width
        $this->totalWidth = $totalWidth;
        $this->labelCellWidth = max(round(($this->totalWidth / $columnsCount) * 2), $this->minWidthLabelCell);
        if ($columnsCount == 2) {
            $this->labelCellWidth = $this->totalWidth / 2;
        }
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
        foreach ($this->reportColumns as $columnId => $columnName)
        {
            $columnName = $this->formatText($columnName);
            //Label column
            if ($countColumns == 0) {
                $this->TCPDF->MultiCell($this->labelCellWidth, $maxCellHeight, $columnName, 1, 'C', true);
                $this->TCPDF->SetXY($posX + $this->labelCellWidth, $posY);
            }
            else
            {
                $this->TCPDF->MultiCell($this->cellWidth, $maxCellHeight, $columnName, 1, 'C', true);
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
}
