<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_PDFReports
 */

/**
 * @see libs/tcpdf
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/PDFReports/config/tcpdf_config.php';
require_once PIWIK_INCLUDE_PATH . '/libs/tcpdf/config/lang/eng.php';
require_once PIWIK_INCLUDE_PATH . '/libs/tcpdf/tcpdf.php';

/**
 *
 * @package Piwik_PDFReports
 */
class Piwik_PDFReports_PDFRenderer extends TCPDF
{
	private $reportFontBold 	   = 'B';
	private $reportSimpleFontSize = 9;
	private $reportHeaderFontSize = 22;
	private $cellHeight = 6;
	private $bottomMargin = 10;
	private $reportWidthPortrait = 180;
	private $reportWidthLandscape = 270;
	private $minWidthLabelCell = 100;
	private $maxColumnCountPortraitOrientation = 7;
	private $logoWidth = 16;
	private $logoHeight = 16;
	private $truncateAfter = 50;
	private $leftSpacesBeforeLogo = 7;
	private $logoImagePosition = array(10,40);
	private $headerTextColor = array(0,89,89);
	private $reportTextColor = array(68,68,68);
	private $tableHeaderBackgroundColor = array(221,234,245);
	private $tableHeaderTextColor 	 = array(84,116,124);
	private $tableHeaderBorderColor = array(193,218,215);
	private $tableBackgroundColor 	 = array(249,250,250);
	private $rowTopBottomBorder = array(231,231,231);
	private $width;
	private $report;
	private $reportMetadata;
	private $reportColumns;
	private $date;
	private $period;
	private $currentPage = 0;
	private $lastTableIsSimpleReport = false;

	public function __construct($websiteName, $prettyDate, $description, $language)
	{
		parent::__construct();
		$this->websiteName = $websiteName;
		$this->prettyDate = $prettyDate;
		$this->description = $description;
		
		switch($language)
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
				$reportFont = 'dejavusans';
				break;
		}
		$this->reportFont = $reportFont;
	}

	public function setReport($reportMetadata, $report, $columns, $rowsMetadata)
	{
		$this->reportMetadata = $reportMetadata;
		$this->report = $report;
		$this->reportColumns = $columns;
		$this->reportRowsMetadata = $rowsMetadata;
	}

	/**
	 * Generates the first page.
	 * Contains logo, date, website name and time generated/time to generate
	 */
	public function paintFirstPage()
	{
		$this->setPrintHeader(false);
		//    	$this->SetMargins($left = , $top, $right=-1, $keepmargins=true)
		$this->AddPage('P');
		$this->AddFont($this->reportFont, '', '', false);
		$this->SetFont($this->reportFont,$this->reportFontBold,$this->reportSimpleFontSize);
		//Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false) {
		$this->Image(Piwik::getLogoPath(), $this->logoImagePosition[0], $this->logoImagePosition[1], 180/$factor=2, 0, $type='', $link='', $align='', $resize=false, $dpi=300);
		$this->Ln(8);

		$websiteTitle = $this->formatText(Piwik_Translate('General_Website') ." " .$this->websiteName);
		$this->SetFont($this->reportFont,'',$this->reportHeaderFontSize + 5);
		$this->SetTextColor($this->headerTextColor[0],$this->headerTextColor[1],$this->headerTextColor[2]);
		$this->Cell(40, 210, $websiteTitle );
		$this->Ln(8*4);
		
		$dateRange = $this->formatText(Piwik_Translate('General_DateRange').": " . $this->prettyDate);
		$this->SetFont($this->reportFont,'',$this->reportHeaderFontSize);
		$this->SetTextColor($this->reportTextColor[0],$this->reportTextColor[1],$this->reportTextColor[2]);
		$this->Cell(40, 210, $dateRange);
		
		$this->Ln(8*20);
		$this->Write(1, $this->formatText($this->description));
		$this->Ln(8);
		$this->SetFont($this->reportFont,'',$this->reportHeaderFontSize);
		$this->Ln();
	}

	/**
	 * Generate a header of page.
	 */
	private function paintReportHeader()
	{
		$currentTableIsSimpleReport = count($this->reportColumns) == 2;
		$reportHasData = count($this->report) > 0;
		// Only a page break before if the current report has some data
		if(($reportHasData
		// or if it is the first report
		|| $this->currentPage == 0)
		&& !($this->lastTableIsSimpleReport
		&& $currentTableIsSimpleReport))
		{
			$this->currentPage++;
			$columnCount = count($this->reportColumns);
			$this->AddPage();
			// Pages without data are always Portrait
			if($reportHasData)
			{
				$this->setPageOrientation( $columnCount > $this->maxColumnCountPortraitOrientation ? 'L' : 'P', '', $this->bottomMargin );
			}
		}
		$this->lastTableIsSimpleReport = $currentTableIsSimpleReport;
		$title = $this->formatText($this->reportMetadata['name']);
		$this->SetFont($this->reportFont,$this->reportFontBold,$this->reportHeaderFontSize);
		$this->SetTextColor($this->headerTextColor[0],$this->headerTextColor[1],$this->headerTextColor[2]);
		$this->Cell(40, 20, $title);
		$this->Ln();
		$this->SetFont($this->reportFont,'',$this->reportSimpleFontSize);
		$this->SetTextColor($this->reportTextColor[0],$this->reportTextColor[1],$this->reportTextColor[2]);
	}

	private function setBorderColor()
	{
		$this->SetDrawColor($this->tableHeaderBorderColor[0],$this->tableHeaderBorderColor[1],$this->tableHeaderBorderColor[2]);
	}

	/**
	 * Draw a table of report
	 */
	public function paintReport()
	{
		$this->paintReportHeader();
		if(empty($this->report))
		{
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
		$this->SetFillColor($this->tableBackgroundColor[0],$this->tableBackgroundColor[1],$this->tableBackgroundColor[2]);
		$this->SetTextColor($this->reportTextColor[0],$this->reportTextColor[1],$this->reportTextColor[2]);
		$this->SetFont('');
			
		$fill = false;
		$logo = $url = false;
		$posY = $posX = 0;
		$leftSpacesBeforeLogo = str_repeat(' ', $this->leftSpacesBeforeLogo);

		// Draw a body of report table
		foreach($this->report as $rowId => $row)
		{
			if (isset($this->reportRowsMetadata[$rowId]['url']))
			{
				$url = $this->reportRowsMetadata[$rowId]['url'];
			}
			foreach ($this->reportColumns as $columnId => $columnName)
			{
				// Label column
				if ($columnId == 'label')
				{
					$isLogoDisplayable = isset($this->reportRowsMetadata[$rowId]['logo']);
					$text = '';
					$posX = $this->GetX();
					$posY = $this->GetY();
					if (isset($row[$columnId]))
					{
						$text = substr($row[$columnId],0,$this->truncateAfter) ;
						if ($isLogoDisplayable)
						{
							$text = $leftSpacesBeforeLogo . $text;
						}
					}
					$text = $this->formatText($text);
					
					$this->Cell($this->labelCellWidth,$this->cellHeight,$text,'LR',0,'L',$fill, $url);

					if($isLogoDisplayable)
					{
						if(isset($this->reportRowsMetadata[$rowId]['logoWidth']))
						{
							$this->logoWidth = $this->reportRowsMetadata[$rowId]['logoWidth'];
						}
						if(isset($this->reportRowsMetadata[$rowId]['logoHeight']))
						{
							$this->logoHeight = $this->reportRowsMetadata[$rowId]['logoHeight'];
						}
						$restoreY = $this->getY();
						$restoreX = $this->getX();
						$this->SetY($posY );
						$this->SetX($posX );
						$topMargin = 1.3;
						// Country flags are not very high, force a bigger top margin
						if($this->logoHeight < 16)
						{
							$topMargin = 2;
						}
						$this->Image(Piwik_Common::getPathToPiwikRoot()."/".$this->reportRowsMetadata[$rowId]['logo'], $posX + ($leftMargin = 2), $posY + $topMargin, $this->logoWidth/4);
						$this->SetXY($restoreX, $restoreY);
					}
				}
				// metrics column
				else
				{
					// No value means 0
					if(empty($row[$columnId]))
					{
						$row[$columnId] = 0;
					}
					$this->Cell($this->cellWidth,$this->cellHeight,$row[$columnId],'LR',0,'L',$fill);
				}
			}

			$this->Ln();

			// Top/Bottom grey border for all cells
			$this->SetDrawColor($this->rowTopBottomBorder[0],$this->rowTopBottomBorder[1],$this->rowTopBottomBorder[2]);
			$this->Cell($this->totalWidth,0,'','T');
			$this->setBorderColor();
			$this->Ln(0.2);

			$fill =! $fill;
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
		foreach($this->reportColumns as $columnName)
		{
			if(strlen($columnName) > strlen($longestColumnName))
			{
				$longestColumnName = $columnName;
			}
		}

		// Decide on page orientation
		$columnsCount = count($this->reportColumns);
		if($columnsCount <= 2)
		{
			$totalWidth = $this->reportWidthPortrait * 2/3;
		}
		else if ($columnsCount > $this->maxColumnCountPortraitOrientation)
		{
			$totalWidth = $this->reportWidthLandscape;
		}
		else
		{
			$totalWidth = $this->reportWidthPortrait;
		}
		// Computes available column width
		$this->totalWidth = $totalWidth;
		$this->labelCellWidth = max(round(($this->totalWidth / $columnsCount) * 2), $this->minWidthLabelCell);
		if($columnsCount == 2)
		{
			$this->labelCellWidth = $this->totalWidth / 2;
		}
		$this->cellWidth = round(($this->totalWidth - $this->labelCellWidth) / ($columnsCount-1));
		$this->totalWidth = $this->labelCellWidth + ($columnsCount-1) * $this->cellWidth;


			
		$this->SetFillColor($this->tableHeaderBackgroundColor[0],$this->tableHeaderBackgroundColor[1],$this->tableHeaderBackgroundColor[2]);
		$this->SetTextColor($this->tableHeaderTextColor[0],$this->tableHeaderTextColor[1],$this->tableHeaderTextColor[2]);
		$this->SetLineWidth(.3);
		$this->setBorderColor();
		$this->SetFont($this->reportFont,$this->reportFontBold);
		$this->SetFillColor(255);
		$this->SetTextColor($this->tableHeaderBackgroundColor[0],$this->tableHeaderBackgroundColor[1],$this->tableHeaderBackgroundColor[2]);
		$this->SetDrawColor(255);

		$posY = $this->GetY();
		$this->MultiCell($this->cellWidth,$this->cellHeight,$longestColumnName,1,'C',true);
		$maxCellHeight = $this->GetY() - $posY;

		$this->SetFillColor($this->tableHeaderBackgroundColor[0],$this->tableHeaderBackgroundColor[1],$this->tableHeaderBackgroundColor[2]);
		$this->SetTextColor($this->tableHeaderTextColor[0],$this->tableHeaderTextColor[1],$this->tableHeaderTextColor[2]);
		$this->SetDrawColor($this->tableHeaderBorderColor[0],$this->tableHeaderBorderColor[1],$this->tableHeaderBorderColor[2]);

		$this->SetXY($initPosX, $posY);

		$countColumns = 0;
		$posX = $initPosX;
		foreach ($this->reportColumns as $columnId => $columnName)
		{
			$columnName = $this->formatText($columnName);
			//Label column
			if ($countColumns == 0)
			{
				$this->MultiCell($this->labelCellWidth,$maxCellHeight,$columnName,1,'C',true);
				$this->SetXY($posX + $this->labelCellWidth, $posY);
			}
			else
			{
				$this->MultiCell($this->cellWidth, $maxCellHeight,$columnName,1,'C',true);
				$this->SetXY($posX + $this->cellWidth, $posY);
			}
			$countColumns++;
			$posX = $this->GetX();
		}
		$this->Ln();
		$this->SetXY($initPosX, $posY + $maxCellHeight);
	}

	/**
	 * Prints a message
	 *
	 * @param $message
	 * @return void
	 */
	private function paintMessage($message)
	{
		$this->SetFont($this->reportFont,$this->reportFontBold,$this->reportSimpleFontSize);
		$this->SetTextColor($this->reportTextColor[0],$this->reportTextColor[1],$this->reportTextColor[2]);
		$message = $this->formatText($message);
		$this->Write("1em", $message);
		$this->Ln();
	}
}
