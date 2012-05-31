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
 *
 * @package Piwik_PDFReports
 */
class Piwik_PDFReports_Controller extends Piwik_Controller
{	
	public function index()
	{
		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		$view->currentUserEmail = Piwik::getCurrentUserEmail();
		$allSites = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess();
		$view->countWebsites = count($allSites);

		$availableReports = Piwik_API_API::getInstance()->getReportMetadata($this->idSite);
		$reportsByCategory = array();
		foreach($availableReports as $report)
		{
			$reportsByCategory[$report['category']][] = $report;
		}
		unset($reportsByCategory['API']);

		$reports = $reportsById = array();
		if(!Piwik::isUserIsAnonymous())
		{
			$reports = Piwik_PDFReports_API::getInstance()->getReports($this->idSite, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = true);
			$reportsById = array();
			foreach($reports as &$report)
			{
				$report['additional_emails'] = str_replace(',',"\n", $report['additional_emails']);
				$report['reports'] = explode(',', str_replace('.','_',$report['reports']));
				$reportsById[$report['idreport']] = $report;
			}
		}

		$view->downloadOutputType = Piwik_PDFReports_API::OUTPUT_DOWNLOAD;
		$columnsCount = 2;
		$view->newColumnAfter = ceil(count($reportsByCategory) / $columnsCount);
		$view->reportsByCategory = $reportsByCategory;
		$view->reportsJSON = Piwik_Common::json_encode($reportsById);
		$periods = array_merge(array('never' => Piwik_Translate('General_Never')),
							Piwik_PDFReports_API::getPeriodToFrequency());
		// Do not display date range in selector
		unset($periods['range']);
		$view->periods = $periods;
		$view->defaultFormat = Piwik_PDFReports::DEFAULT_FORMAT;
		$view->formats = Piwik_ReportRenderer::$availableReportRenderers;
		$view->displayFormats = Piwik_PDFReports_API::getDisplayFormats();
		$view->reports = $reports;
		$view->language = Piwik_LanguagesManager::getLanguageCodeForCurrentUser();
		echo $view->render();
	}
}
