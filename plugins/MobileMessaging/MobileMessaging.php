<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging
 */

/**
 *
 * @package Piwik_MobileMessaging
 */
class Piwik_MobileMessaging extends Piwik_Plugin
{
	const DELEGATED_MANAGEMENT_OPTION = 'MobileMessaging_DelegatedManagement';
	const PROVIDER_OPTION = 'Provider';
	const USERNAME_OPTION = 'Username';
	const PASSWORD_OPTION = 'Password';
	const PHONE_NUMBERS_OPTION = 'PhoneNumbers';
	const DELEGATED_MANAGEMENT_OPTION_DEFAULT = 'false';
	const USER_SETTINGS_POSTFIX_OPTION = '_MobileMessagingSettings';

	const PHONE_NUMBERS_PARAMETER = 'phoneNumbers';

	const MOBILE_TYPE = 'mobile';
	const SMS_FORMAT = 'sms';

	static private $availableParameters = array(
		self::PHONE_NUMBERS_PARAMETER => true,
	);

	static private $managedReportTypes = array(
		self::MOBILE_TYPE => 'plugins/MobileMessaging/images/phone.png'
	);

	static private $managedReportFormats = array(
		self::SMS_FORMAT => 'plugins/MobileMessaging/images/phone.png'
	);

	static private $availableReports = array(
		array(
			'module' => 'MultiSites',
			'action' => 'getAll',
		),
		array(
			'module' => 'MultiSites',
			'action' => 'getOne',
		),
	);

	/**
	 * Return information about this plugin.
	 *
	 * @see Piwik_Plugin
	 *
	 * @return array
	 */
	public function getInformation()
	{
		return array(
            'name' => 'Mobile Messaging Plugin',
			'description' => Piwik_Translate('MobileMessaging_PluginDescription'),
			'homepage' => 'http://piwik.org/',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'license' => 'GPL v3 or later',
			'license_homepage' => 'http://www.gnu.org/licenses/gpl.html',
            'version' => Piwik_Version::VERSION,
			'translationAvailable' => true,
		);
	}

	function getListHooksRegistered()
	{
		return array(
			'AdminMenu.add' => 'addMenu',
			'AssetManager.getJsFiles' => 'getJsFiles',
			'PDFReports.getReportParameters' => 'getReportParameters',
			'PDFReports.validateReportParameters' => 'validateReportParameters',
			'PDFReports.getReportMetadata' => 'getReportMetadata',
			'PDFReports.getReportTypes' => 'getReportTypes',
			'PDFReports.getReportFormats' => 'getReportFormats',
			'PDFReports.getRendererInstance' => 'getRendererInstance',
			'PDFReports.getReportRecipients' => 'getReportRecipients',
			'PDFReports.allowMultipleReports' => 'allowMultipleReports',
			'PDFReports.sendReport' => 'sendReport',
			'template_reportParametersPDFReports' => 'template_reportParametersPDFReports',
		);
	}

	function addMenu()
	{
		Piwik_AddAdminMenu(
			'MobileMessaging_SettingsMenu',
			array('module' => 'MobileMessaging', 'action' => 'index'),
			true
		);
	}

	/**
	 * Get JavaScript files
	 *
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();

		$jsFiles[] = "plugins/MobileMessaging/scripts/MobileMessagingSettings.js";
		$jsFiles[] = "plugins/MobileMessaging/scripts/jquery.select-to-autocomplete.js"; // @review should this go in the LEGALNOTICE file ?
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function validateReportParameters( $notification )
	{
		if(self::manageEvent($notification))
		{
			$parameters = &$notification->getNotificationObject();

			// phone number validation
			$availablePhoneNumbers = Piwik_MobileMessaging_API::getInstance()->getActivatedPhoneNumbers();

			$phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];
			foreach($phoneNumbers as $key => $phoneNumber)
			{
				//@review when a wrong phone number is supplied we silently discard it, should an exception be raised?
				if(!in_array($phoneNumber, $availablePhoneNumbers))
				{
					unset($phoneNumbers[$key]);
				}
			}

			// 'unset' seems to transform the array to an associative array
			$parameters[self::PHONE_NUMBERS_PARAMETER] = array_values($phoneNumbers);
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function getReportMetadata( $notification )
	{
		if(self::manageEvent($notification))
		{
			$availableReportMetadata = &$notification->getNotificationObject();

			$notificationInfo = $notification->getNotificationInfo();
			$idSite = $notificationInfo[Piwik_PDFReports_API::ID_SITE_INFO_KEY];

			foreach(self::$availableReports as $availableReport)
			{
				$reportMetadata = Piwik_API_API::getInstance()->getMetadata(
					$idSite,
					$availableReport['module'],
					$availableReport['action']
				);
				$reportMetadata = reset($reportMetadata);

				$availableReportMetadata[] = $reportMetadata;
			}
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function getReportTypes( $notification )
	{
		$reportTypes = &$notification->getNotificationObject();
		$reportTypes = array_merge($reportTypes, self::$managedReportTypes);
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function getReportFormats( $notification )
	{
		if(self::manageEvent($notification))
		{
			$reportFormats = &$notification->getNotificationObject();
			$reportFormats = array_merge($reportFormats, self::$managedReportFormats);
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function getReportParameters( $notification )
	{
		if(self::manageEvent($notification))
		{
			$availableParameters = &$notification->getNotificationObject();
			$availableParameters = self::$availableParameters;
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function getRendererInstance( $notification )
	{
		if(self::manageEvent($notification))
		{
			$reportRenderer = &$notification->getNotificationObject();
			$reportRenderer = new Piwik_MobileMessaging_ReportRenderer_Sms();
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function allowMultipleReports( $notification )
	{
		if(self::manageEvent($notification))
		{
			$allowMultipleReports = &$notification->getNotificationObject();
			$allowMultipleReports = false;
		}
	}

	function getReportRecipients( $notification )
	{
		if(self::manageEvent($notification))
		{
			$recipients = &$notification->getNotificationObject();
			$notificationInfo = $notification->getNotificationInfo();

			$report = $notificationInfo[Piwik_PDFReports_API::REPORT_KEY];
			$recipients = $report['parameters'][self::PHONE_NUMBERS_PARAMETER];
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	function sendReport( $notification )
	{
		if(self::manageEvent($notification))
		{
			$notificationInfo = $notification->getNotificationInfo();
			$report = $notificationInfo[Piwik_PDFReports_API::REPORT_KEY];
			$websiteName = $notificationInfo[Piwik_PDFReports_API::WEBSITE_NAME_KEY];
			$contents = $notificationInfo[Piwik_PDFReports_API::REPORT_CONTENT_KEY];

			$parameters = $report['parameters'];
			$phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];

			$mobileMessagingAPI = Piwik_MobileMessaging_API::getInstance();
			foreach($phoneNumbers as $phoneNumber)
			{
				$mobileMessagingAPI->sendSMS(
					$contents,
					$phoneNumber,
					$websiteName
				);
			}
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification notification object
	 */
	static public function template_reportParametersPDFReports($notification)
	{
		$out =& $notification->getNotificationObject();

		$view = Piwik_View::factory('ReportParameters');
		$view->reportType = self::MOBILE_TYPE;
		$view->phoneNumbers = Piwik_MobileMessaging_API::getInstance()->getActivatedPhoneNumbers();
		$out .= $view->render();
	}

	private static function manageEvent($notification)
	{
		$notificationInfo = $notification->getNotificationInfo();
		return in_array($notificationInfo[Piwik_PDFReports_API::REPORT_TYPE_INFO_KEY], array_keys(self::$managedReportTypes));
	}

	function install()
	{
		$delegatedManagement = Piwik_GetOption(self::DELEGATED_MANAGEMENT_OPTION);
		if (empty($delegatedManagement))
		{
			Piwik_SetOption(self::DELEGATED_MANAGEMENT_OPTION, self::DELEGATED_MANAGEMENT_OPTION_DEFAULT);
		}
	}

	//@review should we also delete the plugin settings (API credentials, phone numbers) located in table piwik_option?
	function deactivate()
	{
		// delete all mobile reports
		$pdfReportsAPIInstance = Piwik_PDFReports_API::getInstance();
		$reports = $pdfReportsAPIInstance->getReports();

		foreach($reports as $report)
		{
			if ($report['type'] == Piwik_MobileMessaging::MOBILE_TYPE)
			{
				$pdfReportsAPIInstance->deleteReport($report['idreport']);
			}
		}
	}
}
