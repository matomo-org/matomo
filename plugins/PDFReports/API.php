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
 * The PDFReports API lets you manage Scheduled Email reports, as well as generate, download or email any existing report.
 * 
 * "generateReport" will generate the requested report (for a specific date range, website and in the requested language).
 * "sendEmailReport" will send the report by email to the recipients specified for this report. 
 * 
 * You can also get the list of all existing reports via "getReports", create new reports via "addReport", 
 * or manage existing reports with "updateReport" and "deleteReport".
 * See also the documentation about <a href='http://piwik.org/docs/email-reports/' target='_blank'>Scheduled Email reports</a> in Piwik.
 * 
 * @package Piwik_PDFReports
 */
class Piwik_PDFReports_API
{
	const OUTPUT_DOWNLOAD = 1;
	const OUTPUT_SAVE_ON_DISK = 2;

	protected $reportsMetadata = array();
	static private $instance = null;

	/**
	 * @return Piwik_PDFReports_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Creates a new report and schedules it.
	 * 
	 * @param int $idSite 
	 * @param string $description Report description
	 * @param string $period Schedule frequency: day, week or month
	 * @param string $reportFormat 'pdf' or 'html'
	 * @return int $displayFormat see Piwik_PDFReports_API::getDisplayFormats()
	 * @param string $reports Comma separated list of reports
	 * @param bool $emailMe
	 * @param string $additionalEmails Comma separated list of emails
	 *
	 * @return int idReport generated
	 */
	public function addReport( $idSite, $description, $period, $reportFormat, $displayFormat, $reports, $emailMe = true, $additionalEmails = false)
	{
		Piwik::checkUserIsNotAnonymous();
		Piwik::checkUserHasViewAccess($idSite);

		$this->checkPeriod($period);
		$this->checkFormat($reportFormat);
		$this->checkDisplayFormat($displayFormat);
		$description = $this->checkDescription($description);
		$currentUser = Piwik::getCurrentUserLogin();
		$emailMe = (int)$emailMe;
		
		$this->ensureLanguageSetForUser($currentUser);

		$additionalEmails = $this->checkAdditionalEmails($additionalEmails);
		$reports = $this->checkAvailableReports($idSite, $reports);
		
		$db = Zend_Registry::get('db');
		$idReport = $db->fetchOne("SELECT max(idreport) + 1 
								FROM ".Piwik_Common::prefixTable('pdf'));
		if($idReport == false)
		{
			$idReport = 1;
		}
		$db->insert(Piwik_Common::prefixTable('pdf'),
					array( 
						'idreport' => $idReport,
						'idsite' => $idSite,
						'login' => $currentUser,
						'description' => $description,
						'period' => $period,
						'format' => $reportFormat,
						'display_format' => $displayFormat,
						'email_me' => $emailMe,
						'additional_emails' => $additionalEmails,
						'reports' => $reports,
						'ts_created' => Piwik_Date::now()->getDatetime(),
						'deleted' => 0,
					));
		return $idReport;
	} 
	
	private function ensureLanguageSetForUser($currentUser)
	{
		$lang = Piwik_LanguagesManager_API::getInstance()->getLanguageForUser( $currentUser );
		if(empty($lang))
		{
			Piwik_LanguagesManager_API::getInstance()->setLanguageForUser( $currentUser, Piwik_LanguagesManager::getLanguageCodeForCurrentUser() );
		}
	}
	
	/**
	 * Updates an existing report.
	 * 
	 * @see addReport()
	 */
	public function updateReport( $idReport, $idSite, $description, $period, $reportFormat, $displayFormat, $reports, $emailMe = true, $additionalEmails = false)
	{
		Piwik::checkUserIsNotAnonymous();
		Piwik::checkUserHasViewAccess($idSite);

		$pdfReports = $this->getReports($idSite, $periodSearch = false, $idReport);
		$report = reset($pdfReports);
		$idReport = $report['idreport'];
		
		$this->checkPeriod($period);
		$this->checkFormat($reportFormat);
		$this->checkDisplayFormat($displayFormat);
		$description = $this->checkDescription($description);
		$currentUser = Piwik::getCurrentUserLogin();
		$emailMe = (int)$emailMe;
		
		$this->ensureLanguageSetForUser($currentUser);
		
		$additionalEmails = $this->checkAdditionalEmails($additionalEmails);
		
		$reports = $this->checkAvailableReports($idSite, $reports);
		
		Zend_Registry::get('db')->update( Piwik_Common::prefixTable('pdf'), 
					array(
						'description' => $description,
						'period' => $period,
						'format' => $reportFormat,
						'display_format' => $displayFormat,
						'email_me' => $emailMe,
						'additional_emails' => $additionalEmails,
						'reports' => $reports,
						),
					"idreport = '$idReport'"
		);	
		self::$cache = array();
	}
	
	/**
	 * Deletes a specific report
	 * 
	 * @param int $idReport
	 */
	public function deleteReport($idReport)
	{
		$pdfReports = $this->getReports($idSite = false, $periodSearch = false, $idReport);
		$report = reset($pdfReports);
		Piwik::checkUserIsSuperUserOrTheUser($report['login']);
		
		Zend_Registry::get('db')->update( Piwik_Common::prefixTable('pdf'), 
					array(
						'deleted' => 1,
						),
						"idreport = '$idReport'"
		);	
		self::$cache = array();
	}
	
	// static cache storing reports
	public static $cache = array();
	
	/**
	 * Returns the list of reports matching the passed parameters
	 * 
	 * @param int $idSite If specified, will filter reports that belong to a specific idsite
	 * @param string $period If specified, will filter reports that are scheduled for this period (day,week,month)
	 * @param int $idReport If specified, will filter the report that has the given idReport 
	 * @return array
	 * @throws Exception if $idReport was specified but the report wasn't found
	 */
	public function getReports($idSite = false, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = false)
	{
		Piwik::checkUserIsNotAnonymous();

		$cacheKey = (int)$idSite .'.'. (string)$period .'.'. (int)$idReport .'.'. (int)$ifSuperUserReturnOnlySuperUserReports;
		if(isset(self::$cache[$cacheKey]))
		{
			return self::$cache[$cacheKey];
		}

		$sqlWhere = '';
		$bind = array();
		
		// Super user gets all reports back, other users only their own
		if(!Piwik::isUserIsSuperUser()
			|| $ifSuperUserReturnOnlySuperUserReports)
		{
			$sqlWhere .= "AND login = ?";
			$bind[] = Piwik::getCurrentUserLogin();
		}
		
		if(!empty($period))
		{
			$this->checkPeriod($period);
			$sqlWhere .= " AND period = ? ";
			$bind[] = $period;
		}
		if(!empty($idSite))
		{
			Piwik::checkUserHasViewAccess($idSite);
			$sqlWhere .= " AND ".Piwik_Common::prefixTable('site').".idsite = ?";
			$bind[] = $idSite;
		}
		if(!empty($idReport))
		{
			$sqlWhere .= " AND idreport = ?";
			$bind[] = $idReport;
		}
		
		// Joining with the site table to work around pre-1.3 where reports could still be linked to a deleted site
		$reports = Piwik_FetchAll("SELECT * 
								FROM ".Piwik_Common::prefixTable('pdf')."
									JOIN ".Piwik_Common::prefixTable('site')."
									USING (idsite)
								WHERE deleted = 0
									$sqlWhere", $bind);
		// When a specific report was requested and not found, throw an error
		if($idReport !== false
			&& empty($reports))
		{
			throw new Exception("Requested report couldn't be found.");
		}
		// static cache
		self::$cache[$cacheKey] = $reports;
		
		return $reports;
	}

	/**
	 * Generates a report file.
	 *
	 * @param int $idReport ID of the report to generate. If idReport=0 it will generate a report containing all reports
	 * for the specified period & date
	 * @param string $date YYYY-MM-DD
	 * @param bool|false|int $idSite
	 * @param bool|false|string $language If not passed, will use default language.
	 * @param bool|false|int $outputType 1 = download report, 2 = save report to disk, defaults to download
	 * @param bool|false|string $period Defaults to 'day'. If not specified, will default to the report's period set when creating the report
	 * @param bool|string $reportFormat pdf, html
	 * @param bool|false|int $displayFormat see Piwik_PDFReports_API::getDisplayFormats()
	 * @return array
	 */
	public function generateReport($idReport, $date, $idSite = false, $language = false, $outputType = false, $period = false, $reportFormat = false, $displayFormat = false)
	{
		Piwik::checkUserIsNotAnonymous();

		// Load specified language
		if(empty($language))
		{
			$language = Piwik_Translate::getInstance()->getLanguageDefault();
		}
		Piwik_Translate::getInstance()->reloadLanguage($language);

		// Available reports
		$reportMetadata = Piwik_API_API::getInstance()->getReportMetadata($idSite);

		// User who created this report
		$userLogin = false;
		
		// Test template: include all reports
		if($idReport == 0)
		{
			if(empty($period))
			{
				$period = 'day';
			}
			if(empty($reportFormat))
			{
				$reportFormat = Piwik_PDFReports::DEFAULT_FORMAT;
			}
			if(empty($displayFormat))
			{
				$displayFormat = Piwik_PDFReports::DEFAULT_DISPLAY_FORMAT;
			}

			$reports = array();
			foreach($reportMetadata as $report)
			{
				if($report['category'] != 'API')
				{
					$reports[] = $report;
				}
			}

			$description = Piwik_Translate('PDFReports_DefaultContainingAllReports');
		}
		// Template is a custom template
		else
		{
			$pdfReports = $this->getReports($idSite, $_period = false, $idReport);
			$pdfReport = reset($pdfReports);
			
			$userLogin = $pdfReport['login'];
			$reportUniqueIds = explode(',', $pdfReport['reports']);

			$description = $pdfReport['description'];

			// If period wasn't specified, we shall default to the report's period
			if(empty($period))
			{
				$period = 'day';
				if($pdfReport['period'] != 'never')
				{
					$period = $pdfReport['period'];
				}
			}

			// If format wasn't specified, defaults to the report's format
			if(empty($reportFormat))
			{
				$reportFormat = $pdfReport['format'];
				// Handle cases for reports created before the 'format' field
				if(empty($reportFormat))
				{
					$reportFormat = Piwik_PDFReports::DEFAULT_FORMAT;
				}
			}

    		// If $displayFormat wasn't specified, defaults to the report configuration
			if(empty($displayFormat))
			{
				$displayFormat = $pdfReport['display_format'];
			}

			// We need to lookup which reports metadata are registered in this report
			$reports = array();
			foreach($reportMetadata as $metadata)
			{
				if(in_array($metadata['uniqueId'], $reportUniqueIds))
				{
					$reports[] = $metadata;
				}
			}
			
		}

		// prepare the report renderer
		$reportRenderer = Piwik_ReportRenderer::factory($reportFormat);
		$reportRenderer->setLocale($language);
		$reportRenderer->setRenderImageInline($outputType == self::OUTPUT_DOWNLOAD ? true : false);

		$description = str_replace(array("\r", "\n"), ' ', $description);

		// The report will be rendered with the first 23 rows and will aggregate other rows in a summary row
		$filterTruncateGET = Piwik_Common::getRequestVar('filter_truncate', false);

		// 23 rows table fits in one portrait page
		$reportTruncation = 23;
		$_GET['filter_truncate'] = $reportTruncation;

		$websiteName = $prettyDate = false;
		$processedReports = array();
		
		foreach ($reports as $action)
		{
			$apiModule = $action['module'];
			$apiAction = $action['action'];
			$apiParameters = array();
			if(isset($action['parameters']))
			{
				$apiParameters = $action['parameters'];
			}
			
			$mustRestoreGET = false;
			
			// All Websites dashboard should not be truncated in the report
			if($apiModule == 'MultiSites' && $apiAction == 'getAll')
			{
				$mustRestoreGET = $_GET;
				$_GET['filter_truncate'] = false;
				
				// When a view/admin user created a report, workaround the fact that "Super User" 
				// is enforced in Scheduled tasks, and ensure Multisites.getAll only return the websites that this user can access
				if(!empty($userLogin)
					&& $userLogin != Piwik_Config::getInstance()->superuser['login'])
				{
					$_GET['_restrictSitesToLogin'] = $userLogin;
				}
			}
			$report = Piwik_API_API::getInstance()->getProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = false, $apiParameters, $idGoal = false, $language);
			
			if($mustRestoreGET)
			{
				$_GET = $mustRestoreGET;
			}
			$websiteName = $report['website'];
			$prettyDate = $report['prettyDate'];

			$reportMetadata = $report['metadata'];
			$isAggregateReport = !empty($reportMetadata['dimension']);

			$report['displayTable'] = $displayFormat != Piwik_PDFReports::DISPLAY_FORMAT_GRAPHS_ONLY;

			$report['displayGraph'] =
				($isAggregateReport ?
					$displayFormat == Piwik_PDFReports::DISPLAY_FORMAT_GRAPHS_ONLY || $displayFormat == Piwik_PDFReports::DISPLAY_FORMAT_TABLES_AND_GRAPHS
						:
					$displayFormat != Piwik_PDFReports::DISPLAY_FORMAT_TABLES_ONLY)
				&& Piwik::isGdExtensionEnabled()
				&& Piwik_PluginsManager::getInstance()->isPluginActivated('ImageGraph')
				&& !empty($reportMetadata['imageGraphUrl']);

			$processedReports[] = $report;
		}

		// Restore values
		if($filterTruncateGET !== false)
		{
			$_GET['filter_truncate'] = $filterTruncateGET;
		}

		// generate the report
		$reportRenderer->renderFrontPage($websiteName, $prettyDate, $description, $reports );
		array_walk($processedReports, array($reportRenderer, 'renderReport'));

		switch($outputType)
		{
			case self::OUTPUT_SAVE_ON_DISK:
				$outputFilename = 'Email Report - ' . $idReport . '.' . $date . '.' . $idSite . '.' . $language;
				$outputFilename = $reportRenderer->sendToDisk($outputFilename);

				$additionalFiles = array();
				if($reportFormat == 'html')
				{
					foreach ($processedReports as &$report) {
						if($report['displayGraph'])
						{
							$additionalFile = array();
							$additionalFile['filename'] = $report['metadata']['name'].'.png';
							$additionalFile['cid'] = $report['metadata']['uniqueId'];
							$additionalFile['content'] =
									Piwik_ReportRenderer::getStaticGraph(
										$report['metadata']['imageGraphUrl'],
										Piwik_ReportRenderer_Html::IMAGE_GRAPH_WIDTH,
										Piwik_ReportRenderer_Html::IMAGE_GRAPH_HEIGHT
									);
							$additionalFile['mimeType'] = 'image/png';
							$additionalFile['encoding'] = Zend_Mime::ENCODING_BASE64;

							$additionalFiles[] = $additionalFile;
						}
					}
				}

				return array(	$outputFilename,
								$prettyDate,
								$websiteName,
								$reportFormat,
								$additionalFiles,
				);
			break;

			default:
			case self::OUTPUT_DOWNLOAD:
				$reportRenderer->sendToBrowserDownload("$websiteName - $prettyDate - $description");
				break;
		}
	}

	public function sendEmailReport($idReport, $idSite, $period = false, $date = false)
	{
		Piwik::checkUserIsNotAnonymous();
		$reports = $this->getReports($idSite, false, $idReport);
		$report = reset($reports);
		if($report['period'] == 'never')
		{
			$report['period'] = 'day';
		}
		if(!empty($period))
		{
			$report['period'] = $period;
		}
		if(empty($date))
		{
			$date = Piwik_Date::now()->subPeriod(1, $report['period'])->toString();
		}
		

		// Get user emails and languages 
		$emails = self::getEmailsFromString($report['additional_emails']);
		if($report['email_me'] == 1)
		{	
			if(Piwik::getCurrentUserLogin() == $report['login'])
			{
				$emails[] = Piwik::getCurrentUserEmail();
			}
			elseif($report['login'] == Piwik_Config::getInstance()->superuser['login'])
			{
				$emails[] = Piwik::getSuperUserEmail();
			}
			else
			{
				try {
					$user = Piwik_UsersManager_API::getInstance()->getUser($report['login']);
				} catch(Exception $e) {
					return;
				}
				$emails[] = $user['email'];
			}
		}
		$language = Piwik_LanguagesManager_API::getInstance()->getLanguageForUser($report['login']);
		list($outputFilename, $prettyDate, $websiteName, $reportFormat, $additionalFiles) =
			$this->generateReport(
					$idReport, 
					$date,
					$idSite,
					$language,
					self::OUTPUT_SAVE_ON_DISK,
					$report['period']
					);

		$this->sendReportEmail($emails, $outputFilename, $prettyDate, $websiteName, $report, $reportFormat, $additionalFiles);
	}
	
	protected function sendReportEmail($emails, $outputFilename, $prettyDate, $websiteName, $report, $reportFormat, $additionalFiles)
	{
		$periods = self::getPeriodToFrequency();
		$message  = Piwik_Translate('PDFReports_EmailHello');
		$subject = Piwik_Translate('General_Report') . ' '. $websiteName . " - ".$prettyDate;

		if(!file_exists($outputFilename))
		{
			throw new Exception("The report file wasn't found in $outputFilename");
		}
		$filename = basename($outputFilename);
		$handle = fopen($outputFilename, "r");
		$contents = fread($handle, filesize($outputFilename));
		fclose($handle);

		$mail = new Piwik_Mail();
		$mail->setSubject($subject);
		$fromEmailName = Piwik_Config::getInstance()->branding['use_custom_logo'] 
							? Piwik_Translate('CoreHome_WebAnalyticsReports')
							: Piwik_Translate('PDFReports_PiwikReports');
		$fromEmailAddress = Piwik_Config::getInstance()->General['noreply_email_address'];
		$attachmentName = $subject;
		$mail->setFrom($fromEmailAddress, $fromEmailName);

		switch ($reportFormat)
		{
			case 'html':

				// Needed when using images as attachment with cid
				$mail->setType(Zend_Mime::MULTIPART_RELATED);
				$message .= "<br/>" . Piwik_Translate('PDFReports_PleaseFindBelow', array($periods[$report['period']], $websiteName));
				$mail->setBodyHtml($message . "<br/><br/>". $contents);
				break;

			default:
			case 'pdf':
				$message .= "\n" . Piwik_Translate('PDFReports_PleaseFindAttachedFile', array($periods[$report['period']], $websiteName));
				$mail->setBodyText($message);
				$mail->createAttachment(	$contents,
											'application/pdf',
											Zend_Mime::DISPOSITION_INLINE,
											Zend_Mime::ENCODING_BASE64,
											$attachmentName.'.pdf'
				);
				break;
		}

		foreach($additionalFiles as $additionalFile)
		{
			$fileContent = $additionalFile['content'];
			$at = $mail->createAttachment(
				$fileContent,
				$additionalFile['mimeType'],
				Zend_Mime::DISPOSITION_INLINE,
				$additionalFile['encoding'],
				$additionalFile['filename']
			);
			$at->id = $additionalFile['cid'];

			unset($fileContent);
		}

		foreach ($emails as $email)
		{
			$mail->addTo($email);

			try {
				$mail->send();
			} catch(Exception $e) {

				// If running from piwik.php with debug, we ignore the 'email not sent' error
				if(!isset($GLOBALS['PIWIK_TRACKER_DEBUG']) || !$GLOBALS['PIWIK_TRACKER_DEBUG'])
				{
					throw new Exception("An error occured while sending '$filename' ".
										" to ". implode(', ',$mail->getRecipients()). 
									". Error was '". $e->getMessage()."'");
				}
			}
			$mail->clearRecipients();
		}
		// Update flag in DB
		Zend_Registry::get('db')->update( Piwik_Common::prefixTable('pdf'),
										  array( 'ts_last_sent' => Piwik_Date::now()->getDatetime() ),
										  "idreport = " . $report['idreport']
		);

		// If running from piwik.php with debug, do not delete the PDF after sending the email  
		if(!isset($GLOBALS['PIWIK_TRACKER_DEBUG']) || !$GLOBALS['PIWIK_TRACKER_DEBUG'])
		{
			@chmod($outputFilename, 0600);
			@unlink($outputFilename);
		}
	}
	
	private function checkAdditionalEmails($additionalEmails)
	{
		if(empty($additionalEmails))
		{
			return '';
		}
		$additionalEmails = self::getEmailsFromString($additionalEmails);
		foreach($additionalEmails as &$email)
		{
			$email = trim($email);
			if(!Piwik::isValidEmailString($email))
			{
				throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidEmail') . ' ('.$email.')');
			}
		}
		$additionalEmails = implode(',',$additionalEmails);
		return $additionalEmails;
	}

	static protected function getEmailsFromString($additionalEmails)
	{
		if(empty($additionalEmails))
		{
			return array();
		}
		$additionalEmails = explode(',', trim($additionalEmails));
		$additionalEmails = array_filter($additionalEmails, 'strlen');
		return $additionalEmails;
	}
	
	private function checkDescription($description)
	{
		return substr($description, 0, 250);
	}
	
	private function checkAvailableReports($idSite, $reports)
	{
		$availableReports = Piwik_API_API::getInstance()->getReportMetadata($idSite);
		$availableReportIds = array();
		foreach($availableReports as $report)
		{
			$availableReportIds[] = $report['uniqueId'];
		}
		$reports = explode(',', $reports);
		$reports = array_filter($reports, 'strlen');
		foreach($reports as $report)
		{
			if(!in_array($report, $availableReportIds))
			{
				throw new Exception("Report $report is unknown.");
			}
		}
		$reports = implode(',', $reports);
		return $reports;
	}
	
	private function checkPeriod($period)
	{
		$availablePeriods = array('day', 'week', 'month', 'never');
		if(!in_array($period, $availablePeriods))
		{
			throw new Exception(Piwik_Translate("Period schedule must be one of the following: " . implode(', ', $availablePeriods)));
		}
	}

	private function checkFormat($format)
	{
		$availableReportRenderers = array_keys(Piwik_ReportRenderer::$availableReportRenderers);
		if(!in_array($format, $availableReportRenderers))
		{
			throw new Exception(
				Piwik_TranslateException(
					'General_ExceptionInvalidReportRendererFormat',
					array($format, implode(', ', $availableReportRenderers))
				)
			);
		}
	}

	private function checkDisplayFormat($format)
	{
		$availableDisplayFormats = array_keys(Piwik_PDFReports_API::getDisplayFormats());
		if(!in_array($format, $availableDisplayFormats))
		{
			throw new Exception(
				Piwik_TranslateException(
					// General_ExceptionInvalidAggregateReportsFormat should be named General_ExceptionInvalidDisplayFormat
					'General_ExceptionInvalidAggregateReportsFormat',
					array($format, implode(', ', $availableDisplayFormats))
				)
			);
		}
	}

	/**
	 * @ignore
	 */
	static public function getPeriodToFrequency()
	{
		$periods = array(
			'day' => Piwik_Translate('General_Daily'),
			'week' => Piwik_Translate('General_Weekly'),
			'month' => Piwik_Translate('General_Monthly'),
			'range' => Piwik_Translate('General_DateRangeInPeriodList'),
		);
		return $periods;
	}

	/**
	 * @ignore
	 */
	static public function getDisplayFormats()
	{
		$periods = array(
			// PDFReports_AggregateReportsFormat_TablesOnly should be named PDFReports_DisplayFormat_GraphsOnlyForKeyMetrics
			Piwik_PDFReports::DISPLAY_FORMAT_GRAPHS_ONLY_FOR_KEY_METRICS => Piwik_Translate('PDFReports_AggregateReportsFormat_TablesOnly'),
			// PDFReports_AggregateReportsFormat_GraphsOnly should be named PDFReports_DisplayFormat_GraphsOnly
			Piwik_PDFReports::DISPLAY_FORMAT_GRAPHS_ONLY => Piwik_Translate('PDFReports_AggregateReportsFormat_GraphsOnly'),
			// PDFReports_AggregateReportsFormat_TablesAndGraphs should be named PDFReports_DisplayFormat_TablesAndGraphs
			Piwik_PDFReports::DISPLAY_FORMAT_TABLES_AND_GRAPHS => Piwik_Translate('PDFReports_AggregateReportsFormat_TablesAndGraphs'),
			Piwik_PDFReports::DISPLAY_FORMAT_TABLES_ONLY => Piwik_Translate('PDFReports_DisplayFormat_TablesOnly'),
		);
		return $periods;
	}
}
