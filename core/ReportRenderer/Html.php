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
 *
 * @package Piwik_ReportRenderer
 */
class Piwik_ReportRenderer_Html extends Piwik_ReportRenderer
{
	const REPORT_TITLE_TEXT_SIZE = 11;
	const REPORT_TABLE_HEADER_TEXT_SIZE = 11;
	const REPORT_TABLE_ROW_TEXT_SIZE = 11;
	const REPORT_BACK_TO_TOP_TEXT_SIZE = 9;

	private $rendering = "";

	public function setLocale($locale)
	{
		//Nothing to do
	}

	public function sendToDisk($filename)
	{
		$this->epilogue();

		$filename = Piwik_ReportRenderer::appendExtension($filename, "html");
		$outputFilename = Piwik_ReportRenderer::getOutputPath($filename);

		$emailReport = fopen($outputFilename, "w");

		if (!$emailReport) {
			throw new Exception ("The file : " . $outputFilename . " can not be opened in write mode.");
		}

		fwrite($emailReport, $this->rendering);
		fclose($emailReport);

		return $outputFilename;
	}

	public function sendToBrowserDownload($filename)
	{
		$this->epilogue();

		$filename = Piwik_ReportRenderer::appendExtension($filename, "html");

		Piwik::overrideCacheControlHeaders();
		header('Content-Description: File Transfer');
		header('Content-Type: text/html');
		header('Content-Disposition: attachment; filename="'.basename($filename).'";');
		header('Content-Length: '.strlen($this->rendering));
		echo $this->rendering;
	}

	private function epilogue()
	{
		$smarty = new Piwik_Smarty();
		$this->rendering .= $smarty->fetch(self::prefixTemplatePath("html_report_footer.tpl"));
	}

	public function renderFrontPage($websiteName, $prettyDate, $description, $reportMetadata)
	{
		$smarty = new Piwik_Smarty();
		$this->assignCommonParameters($smarty);

		$smarty->assign("websiteName", $websiteName);
		$smarty->assign("prettyDate", $prettyDate);
		$smarty->assign("description", $description);
		$smarty->assign("reportMetadata", $reportMetadata);

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
	}

	public function renderReport($processedReport)
	{
		$smarty = new Piwik_Smarty();
		$this->assignCommonParameters($smarty);

		$smarty->assign("reportName", $processedReport['metadata']['name']);
		$smarty->assign("reportId", $processedReport['metadata']['uniqueId']);
		$smarty->assign("reportColumns", $processedReport['columns']);
		$smarty->assign("reportRows", $processedReport['reportData']);
		$smarty->assign("reportRowsMetadata", $processedReport['reportMetadata']);

		$this->rendering .= $smarty->fetch(self::prefixTemplatePath("html_report_body.tpl"));
	}

	private static function prefixTemplatePath($templateFile)
	{
		return PIWIK_USER_PATH . "/plugins/CoreHome/templates/" . $templateFile;
	}
}
