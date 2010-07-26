<?php
class Piwik_PDFReports_Controller extends Piwik_Controller
{	
    public function index()
	{
		$view = Piwik_View::factory('index');
        $this->setGeneralVariablesView($view);
		$view->currentUserEmail = Piwik::getCurrentUserEmail();
		
		$availableReports = Piwik_API_API::getInstance()->getReportMetadata($this->idSite);
		$reportsByCategory = array();
		foreach($availableReports as $report)
		{
			$reportsByCategory[$report['category']][] = $report;
		}
		
		$reports = Piwik_PDFReports_API::getInstance()->getReports($this->idSite);
		$reportsById = array();
		foreach($reports as &$report)
		{
			$report['additional_emails'] = str_replace(',',"\n", $report['additional_emails']);
			$report['reports'] = explode(',', str_replace('.','_',$report['reports']));
			$reportsById[$report['idreport']] = $report;
		}

		$view->pdfDownloadOutputType = Piwik_PDFReports_API::OUTPUT_PDF_DOWNLOAD;
		$columnsCount = 2;
		$view->newColumnAfter = round(count($availableReports) / $columnsCount);
		$view->reportsByCategory = $reportsByCategory;
		$view->reportsJSON = json_encode($reportsById);
		$view->periods = Piwik_PDFReports::getPeriodToFrequency();
		$view->reports = $reports;
		echo $view->render();
	}
	
	
}
