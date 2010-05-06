<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Installation
 */

/**
 * Installation controller
 *
 * @package Piwik_Installation
 */
class Piwik_Installation_Controller extends Piwik_Controller
{
	// public so plugins can add/delete installation steps
	public $steps = array(
			'welcome'				=> 'Installation_Welcome',
			'systemCheck'			=> 'Installation_SystemCheck',
			'databaseSetup'			=> 'Installation_DatabaseSetup',
			'databaseCheck'			=> 'Installation_DatabaseCheck',
			'tablesCreation'		=> 'Installation_Tables',
			'generalSetup'			=> 'Installation_GeneralSetup',
			'firstWebsiteSetup'		=> 'Installation_SetupWebsite',
			'displayJavascriptCode'	=> 'Installation_JsTag',
			'finished'				=> 'Installation_Congratulations',
		);

	protected $pathView = 'Installation/templates/';

	protected $session;

	public function __construct()
	{
		$this->session = new Zend_Session_Namespace('Piwik_Installation');
		if(!isset($this->session->currentStepDone))
		{
			$this->session->currentStepDone = '';
			$this->session->skipThisStep = array();
		}

		Piwik_PostEvent('InstallationController.construct', $this);
	}

	/**
	 * Get installation steps
	 *
	 * @return array installation steps
	 */
	public function getInstallationSteps()
	{
		return $this->steps;
	}

	/**
	 * Get default action (first installation step)
	 *
	 * @return string function name
	 */
	function getDefaultAction()
	{
		$steps = array_keys($this->steps);
		return $steps[0];
	}

	/**
	 * Installation Step 1: Welcome
	 */
	function welcome($message)
	{
		$view = new Piwik_Installation_View(
						$this->pathView . 'welcome.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$view->newInstall = !file_exists(Piwik_Config::getDefaultUserConfigPath());
		$view->errorMessage = $message;
		$this->skipThisStep( __FUNCTION__ );
		$view->showNextStep = $view->newInstall;
		$this->session->currentStepDone = __FUNCTION__;
		echo $view->render();
	}

	/**
	 * Installation Step 2: System Check
	 */
	function systemCheck()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'systemCheck.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );

		$view->infos = self::getSystemInformation();
		$view->helpMessages = array(
			'zlib'            => 'Installation_SystemCheckZlibHelp',
			'SPL'             => 'Installation_SystemCheckSplHelp',
			'iconv'           => 'Installation_SystemCheckIconvHelp',
			'dom'             => 'Installation_SystemCheckDomHelp',
			'set_time_limit'  => 'Installation_SystemCheckTimeLimitHelp',
			'mail'            => 'Installation_SystemCheckMailHelp',
			'parse_ini_file'  => 'Installation_SystemCheckParseIniFileHelp',
			'debug_backtrace' => 'Installation_SystemCheckDebugBacktraceHelp',
			'create_function' => 'Installation_SystemCheckCreateFunctionHelp',
		);

		$view->problemWithSomeDirectories = (false !== array_search(false, $view->infos['directories']));

		$view->showNextStep = !$view->problemWithSomeDirectories
							&& $view->infos['phpVersion_ok']
							&& count($view->infos['adapters'])
							&& !count($view->infos['missing_extensions'])
							&& !count($view->infos['missing_functions'])
						;

		$this->session->currentStepDone = __FUNCTION__;

		echo $view->render();
	}

	/**
	 * Installation Step 3: Database Set-up
	 */
	function databaseSetup()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		// case the user hits the back button
		$this->session->skipThisStep = array(
			'firstWebsiteSetup' => false,
			'displayJavascriptCode' => false,
		);

		$view = new Piwik_Installation_View(
						$this->pathView . 'databaseSetup.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );

		$view->showNextStep = false;

		$form = new Piwik_Installation_FormDatabaseSetup();

		if($form->validate())
		{
			$adapter = $form->getSubmitValue('adapter');
			$port = Piwik_Db::getDefaultPortForAdapter($adapter);

			$dbInfos = array(
				'host' 			=> $form->getSubmitValue('host'),
				'username' 		=> $form->getSubmitValue('username'),
				'password' 		=> $form->getSubmitValue('password'),
				'dbname' 		=> $form->getSubmitValue('dbname'),
				'tables_prefix' => $form->getSubmitValue('tables_prefix'),
				'adapter' 		=> $adapter,
				'port'			=> $port,
			);

			if(($portIndex = strpos($dbInfos['host'], '/')) !== false)
			{
				// unix_socket=/path/sock.n
				$dbInfos['port'] = substr($dbInfos['host'], $portIndex);
				$dbInfos['host'] = '';
			}
			else if(($portIndex = strpos($dbInfos['host'], ':')) !== false)
			{
				// host:port
				$dbInfos['port'] = substr($dbInfos['host'], $portIndex + 1 );
				$dbInfos['host'] = substr($dbInfos['host'], 0, $portIndex);
			}

			try{
				try {
					Piwik::createDatabaseObject($dbInfos);
					$this->session->databaseCreated = true;
				} catch (Zend_Db_Adapter_Exception $e) {
					$db = Piwik_Db::factory($adapter, $dbInfos);

					// database not found, we try to create  it
					if($db->isErrNo($e, '1049'))
					{
						$dbInfosConnectOnly = $dbInfos;
						$dbInfosConnectOnly['dbname'] = null;
						Piwik::createDatabaseObject($dbInfosConnectOnly);
						Piwik::createDatabase($dbInfos['dbname']);
						$this->session->databaseCreated = true;
					}
					else
					{
						throw $e;
					}
				}

				Piwik::checkDatabaseVersion();
				$this->session->databaseVersionOk = true;

				$this->session->db_infos = $dbInfos;
				$this->redirectToNextStep( __FUNCTION__ );
			} catch(Exception $e) {
				$view->errorMessage = $e->getMessage();
			}
		}
		$view->addForm($form);

		echo $view->render();
	}

	/**
	 * Installation Step 4: Database Check
	 */
	function databaseCheck()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		$view = new Piwik_Installation_View(
						$this->pathView . 'databaseCheck.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
					
		$error = false;
		$this->skipThisStep( __FUNCTION__ );

		if(isset($this->session->databaseVersionOk)
			&& $this->session->databaseVersionOk === true)
		{
			$view->databaseVersionOk = true;
		}
		else
		{
			$error = true;
		}

		if(isset($this->session->databaseCreated)
			&& $this->session->databaseCreated === true)
		{
			$dbInfos = $this->session->db_infos;
			$view->databaseName = $dbInfos['dbname'];
			$view->databaseCreated = true;
		}
		else
		{
			$error = true;
		}

		$this->createDbFromSessionInformation();
		$db = Zend_Registry::get('db');

		try {
			$db->checkClientVersion();
		} catch(Exception $e) {
			$view->clientVersionWarning = $e->getMessage();
			$error = true;
		}

		if(!Piwik::isDatabaseConnectionUTF8())
		{
			$dbInfos = $this->session->db_infos;
			$dbInfos['charset'] = 'utf8';
			$this->session->db_infos = $dbInfos;
		}

		$view->showNextStep = true;
		$this->session->currentStepDone = __FUNCTION__;
	
		if($error === false)
		{
			$this->redirectToNextStep(__FUNCTION__);
		}
		echo $view->render();
	}

	/**
	 * Installation Step 5: Table Creation
	 */
	function tablesCreation()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'tablesCreation.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );
		$this->createDbFromSessionInformation();

		if(Piwik_Common::getRequestVar('deleteTables', 0, 'int') == 1)
		{
			Piwik::dropTables();
			$view->existingTablesDeleted = true;

			// when the user decides to drop the tables then we dont skip the next steps anymore
			// workaround ZF-1743
			$tmp = $this->session->skipThisStep;
			$tmp['firstWebsiteSetup'] = false;
			$tmp['displayJavascriptCode'] = false;
			$this->session->skipThisStep = $tmp;
		}

		$tablesInstalled = Piwik::getTablesInstalled();
		$tablesToInstall = Piwik::getTablesNames();
		$view->tablesInstalled = '';
		if(count($tablesInstalled) > 0)
		{
			// we have existing tables
			$view->tablesInstalled = implode(', ', $tablesInstalled);
			$view->someTablesInstalled = true;

			$minimumCountPiwikTables = 12;
			$baseTablesInstalled = preg_grep('/archive_numeric|archive_blob/', $tablesInstalled, PREG_GREP_INVERT);

			Piwik::createAccessObject();
			Piwik::setUserIsSuperUser();

			if(count($baseTablesInstalled) >= $minimumCountPiwikTables &&
				count(Piwik_SitesManager_API::getInstance()->getAllSitesId()) > 0 &&
				count(Piwik_UsersManager_API::getInstance()->getUsers()) > 0)
			{
				$view->showReuseExistingTables = true;
				// when the user reuses the same tables we skip the website creation step
				// workaround ZF-1743
				$tmp = $this->session->skipThisStep;
				$tmp['firstWebsiteSetup'] = true;
				$tmp['displayJavascriptCode'] = true;
				$this->session->skipThisStep = $tmp;
			}
		}
		else
		{
			Piwik::createTables();
			Piwik::createAnonymousUser();

			$updater = new Piwik_Updater();
			$updater->recordComponentSuccessfullyUpdated('core', Piwik_Version::VERSION);
			$view->tablesCreated = true;
			$view->showNextStep = true;
		}

		$this->session->currentStepDone = __FUNCTION__;
		echo $view->render();
	}

	/**
	 * Installation Step 6: General Set-up (superuser login/password/email and subscriptions)
	 */
	function generalSetup()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'generalSetup.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );

		$form = new Piwik_Installation_FormGeneralSetup();

		if($form->validate())
		{
			$superUserInfos = array(
				'login' 		=> $form->getSubmitValue('login'),
				'password' 		=> md5( $form->getSubmitValue('password') ),
				'email' 		=> $form->getSubmitValue('email'),
				'salt'			=> Piwik_Common::generateUniqId(),
			);

			$this->session->superuser_infos = $superUserInfos;

			$host = 'http://api.piwik.org/1.0/';
			$host .= 'subscribeNewsletter/';
			$params = array(
				'email' => $form->getSubmitValue('email'),
				'security' => $form->getSubmitValue('subscribe_newsletter_security'),
				'community' => $form->getSubmitValue('subscribe_newsletter_community'),
				'url' => Piwik_Url::getCurrentUrlWithoutQueryString(),
			);
			if($params['security'] == '1'
				|| $params['community'] == '1')
			{
				if( !isset($params['security']))  { $params['security'] = '0'; }
				if( !isset($params['community'])) { $params['community'] = '0'; }
				$url = $host . '?' . http_build_query($params, '', '&');
				try {
					Piwik_Http::sendHttpRequest($url, $timeout = 2);
				} catch(Exception $e) {
					// e.g., disable_functions = fsockopen; allow_url_open = Off
				}
			}
			$this->redirectToNextStep( __FUNCTION__ );
		}
		$view->addForm($form);

		echo $view->render();
	}

	/**
	 * Installation Step 7: Configure first web-site
	 */
	public function firstWebsiteSetup()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'firstWebsiteSetup.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );

		$form = new Piwik_Installation_FormFirstWebsiteSetup();
		if( !isset($this->session->generalSetupSuccessMessage))
		{
			$view->displayGeneralSetupSuccess = true;
			$this->session->generalSetupSuccessMessage = true;
		}

		$this->initObjectsToCallAPI();
		if($form->validate())
		{
			$name = urlencode($form->getSubmitValue('siteName'));
			$url = urlencode($form->getSubmitValue('url'));


			$request = new Piwik_API_Request("
							method=SitesManager.addSite
							&siteName=$name
							&urls=$url
							&format=original
						");

			try {
				$result = $request->process();
				$this->session->site_idSite = $result;
				$this->session->site_name = $name;
				$this->session->site_url = $url;

				$this->redirectToNextStep( __FUNCTION__ );
			} catch(Exception $e) {
				$view->errorMessage = $e->getMessage();
			}

		}
		$view->addForm($form);
		echo $view->render();
	}

	/**
	 * Installation Step 8: Display JavaScript tracking code
	 */
	public function displayJavascriptCode()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'displayJavascriptCode.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );

		if( !isset($this->session->firstWebsiteSetupSuccessMessage))
		{
			$view->displayfirstWebsiteSetupSuccess = true;
			$this->session->firstWebsiteSetupSuccessMessage = true;
		}

		$view->websiteName = urldecode($this->session->site_name);

		$jsTag = Piwik::getJavascriptCode($this->session->site_idSite, Piwik_Url::getCurrentUrlWithoutFileName());

		$view->javascriptTag = $jsTag;
		$view->showNextStep = true;

		$this->session->currentStepDone = __FUNCTION__;
		echo $view->render();
	}

	/**
	 * Installation Step 9: Finished!
	 */
	public function finished()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'finished.tpl',
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );

		if(!file_exists(Piwik_Config::getDefaultUserConfigPath()))
		{
			$this->writeConfigFileFromSession();
		}

		$view->showNextStep = false;

		$this->session->currentStepDone = __FUNCTION__;
		echo $view->render();
	}

	/**
	 * Instantiate access and log objects
	 */
	protected function initObjectsToCallAPI()
	{
		// connect to the database using the DB infos currently in the session
		$this->createDbFromSessionInformation();

		Piwik::createAccessObject();
		Piwik::setUserIsSuperUser();
		Piwik::createLogObject();
	}

	/**
	 * Create database connection from session-store
	 */
	protected function createDbFromSessionInformation()
	{
		$dbInfos = $this->session->db_infos;
		Zend_Registry::get('config')->disableSavingConfigurationFileUpdates();
		Zend_Registry::get('config')->database = $dbInfos;
		Piwik::createDatabaseObject($dbInfos);
	}

	/**
	 * Write configuration file from session-store
	 */
	protected function writeConfigFileFromSession()
	{
		if(!isset($this->session->superuser_infos)
			|| !isset($this->session->db_infos))
		{
			return;
		}
		$config = Zend_Registry::get('config');
		$config->superuser = $this->session->superuser_infos;
		$dbInfos = $this->session->db_infos;
		$config->database = $dbInfos;

		unset($this->session->superuser_infos);
		unset($this->session->db_infos);
	}

	/**
	 * Save language selection in session-store
	 */
	public function saveLanguage()
	{
		$language = Piwik_Common::getRequestVar('language');
		Piwik_LanguagesManager_API::getInstance()->setLanguageForSession($language);
		Piwik_Url::redirectToReferer();
	}

	/**
	 * The previous step is valid if it is either
	 * - any step before (OK to go back)
	 * - the current step (case when validating a form)
	 * If step is invalid, then exit.
	 *
	 * @param string $currentStep Current step
	 */
	protected function checkPreviousStepIsValid( $currentStep )
	{
		$error = false;

		if(empty($this->session->currentStepDone))
		{
			$error = true;
		}
		else if($currentStep == 'finished' && $this->session->currentStepDone == 'finished')
		{
			// ok to refresh this page or use language selector
		}
		else
		{
			if(file_exists(Piwik_Config::getDefaultUserConfigPath()))
			{
				$error = true;
			}

			$steps = array_keys($this->steps);

			// the currentStep
			$currentStepId = array_search($currentStep, $steps);

			// the step before
			$previousStepId = array_search($this->session->currentStepDone, $steps);

			// not OK if currentStepId > previous+1
			if( $currentStepId > $previousStepId + 1 )
			{
				$error = true;
			}
		}
		if($error)
		{
			Piwik_Login_Controller::clearSession();
			$message = Piwik_Translate('Installation_ErrorInvalidState',
						array( '<br /><b>',
								'</b>',
								'<a href=\''.Piwik_Url::getCurrentUrlWithoutFileName().'\'>',
								'</a>')
					);
			Piwik::exitWithErrorMessage( $message );
		}
	}

	/**
	 * Redirect to next step
	 *
	 * @param string Current step
	 * @return none
	 */
	protected function redirectToNextStep($currentStep)
	{
		$steps = array_keys($this->steps);
		$this->session->currentStepDone = $currentStep;
		$nextStep = $steps[1 + array_search($currentStep, $steps)];
		Piwik::redirectToModule('Installation' , $nextStep);
	}

	/**
	 * Skip this step (typically to mark the current function as completed)
	 *
	 * @param string function name
	 */
	protected function skipThisStep( $step )
	{
		$skipThisStep = $this->session->skipThisStep;
		if(isset($skipThisStep[$step]) && $skipThisStep[$step])
		{
			$this->redirectToNextStep($step);
		}
	}

	/**
	 * Get system information
	 */
	public static function getSystemInformation()
	{
		$minimumPhpVersion = Zend_Registry::get('config')->General->minimum_php_version;
		$minimumMemoryLimit = Zend_Registry::get('config')->General->minimum_memory_limit;

		$infos = array();

		$infos['directories'] = Piwik::checkDirectoriesWritable();
		Piwik::createHtAccessFiles();

		$infos['phpVersion_minimum'] = $minimumPhpVersion;
		$infos['phpVersion'] = phpversion();
		$infos['phpVersion_ok'] = version_compare( $minimumPhpVersion, $infos['phpVersion']) === -1;

		// critical errors
		$extensions = @get_loaded_extensions();
		$needed_extensions = array(
			'zlib',
			'SPL',
			'iconv',
			'Reflection',
		);
		$infos['needed_extensions'] = $needed_extensions;
		$infos['missing_extensions'] = array();
		foreach($needed_extensions as $needed_extension)
		{
			if(!in_array($needed_extension, $extensions))
			{
				$infos['missing_extensions'][] = $needed_extension;
			}
		}

		$infos['pdo_ok'] = false;
		if(in_array('PDO', $extensions))
		{
			$infos['pdo_ok'] = true;
		}

		$infos['adapters'] = Piwik_Db::getAdapters();

		$infos['json'] = false;
		if(in_array('json', $extensions))
		{
			$infos['json'] = true;
		}

		$infos['xml'] = false;
		if(in_array('xml', $extensions))
		{
			$infos['xml'] = true;
		}

		$needed_functions = array(
			'debug_backtrace',
			'create_function',
		);
		$infos['needed_functions'] = $needed_functions;
		$infos['missing_functions'] = array();
		foreach($needed_functions as $needed_function)
		{
			if(!self::functionExists($needed_function))
			{
				$infos['missing_functions'][] = $needed_function;
			}
		}

		// warnings
		$desired_functions = array(
			'set_time_limit',
			'mail',
			'parse_ini_file',
		);
		$infos['desired_functions'] = $desired_functions;
		$infos['missing_desired_functions'] = array();
		foreach($desired_functions as $desired_function)
		{
			if(!self::functionExists($desired_function))
			{
				$infos['missing_desired_functions'][] = $desired_function;
			}
		}

		$infos['openurl'] = Piwik_Http::getTransportMethod();

		$infos['gd_ok'] = false;
		if (in_array('gd', $extensions))
		{
			$gdInfo = gd_info();
			$infos['gd_version'] = $gdInfo['GD Version'];
			preg_match('/([0-9]{1})/', $gdInfo['GD Version'], $gdVersion);
			if($gdVersion[0] >= 2)
			{
				$infos['gd_ok'] = true;
			}
		}

		$infos['hasMbstring'] = false;
		$infos['multibyte_ok'] = true;
		if(function_exists('mb_internal_encoding'))
		{
			$infos['hasMbstring'] = true;
			if (((int) ini_get('mbstring.func_overload')) != 0)
			{
				$infos['multibyte_ok'] = false;
			}
		}

		/**
		 * @see http://php.net/ip2long
		 */
		$infos['isIpv4'] = true;
		if(strpos($_SERVER['REMOTE_ADDR'], ':') !== false)
		{
			$infos['isIpv4'] = false;
		}

		$infos['serverVersion'] = addslashes($_SERVER['SERVER_SOFTWARE']);
		$infos['serverOs'] = @php_uname();
		$infos['serverTime'] = date('H:i:s');

		$infos['registerGlobals_ok'] = ini_get('register_globals') == 0;
		$infos['memoryMinimum'] = $minimumMemoryLimit;

		$infos['memory_ok'] = true;
		// on windows the ini_get is not working?
		$infos['memoryCurrent'] = '?M';

		$raised = Piwik::raiseMemoryLimitIfNecessary();
		if(	$memoryValue = Piwik::getMemoryLimitValue() )
		{
			$infos['memoryCurrent'] = $memoryValue.'M';
			$infos['memory_ok'] = $memoryValue >= $minimumMemoryLimit;
		}

		$infos['isWindows'] = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';

		$infos['protocol_ok'] = true;
		$infos['protocol'] = self::getProtocolInformation();
		if(Piwik_Url::getCurrentScheme() == 'http' &&
			$infos['protocol'] !== null)
		{
			$infos['protocol_ok'] = false;
		}

		$integrityInfo = Piwik::getFileIntegrityInformation();
		$infos['integrity'] = $integrityInfo[0];
		
		$infos['integrityErrorMessages'] = array();
		if(isset($integrityInfo[1]))
		{
			if($infos['integrity'] == false)
			{
				$infos['integrityErrorMessages'][] = '<b>'.Piwik_Translate('General_FileIntegrityWarningExplanation').'</b>';
			}
			$infos['integrityErrorMessages'] += array_slice($integrityInfo, 1);
		}

		$infos['timezone'] = Piwik::isTimezoneSupportEnabled();
		return $infos;
	}

	/**
	 * Get protocol information, with the exception of HTTPS
	 *
	 * @return string protocol information
	 */
	public static function getProtocolInformation()
	{
		if(Piwik_Common::getRequestVar('clientProtocol', 'http', 'string') == 'https')
		{
			return 'https';
		}

		if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
		{
			return 'SERVER_PORT=443';
		}

		if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
		{
			return 'X-Forwarded-Proto';
		}

		if(isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && strtolower($_SERVER['HTTP_X_FORWARDED_SCHEME']) == 'https')
		{
			return 'X-Forwarded-Scheme';
		}

		if(isset($_SERVER['HTTP_X_URL_SCHEME']) && strtolower($_SERVER['HTTP_X_URL_SCHEME']) == 'https')
		{
			return 'X-Url-Scheme';
		}

		return null;
	}

	/**
	 * Test if function exists.  Also handles case where function is disabled via Suhosin.
	 *
	 * @param string $functionName Function name
	 * @return bool True if function exists (not disabled); False otherwise.
	 */
	public static function functionExists($functionName)
	{
		if(extension_loaded('suhosin'))
		{
			$blacklist = @ini_get("suhosin.executor.func.blacklist");
			if(!empty($blacklist))
			{
				$blacklistFunctions = array_map('strtolower', array_map('trim', explode(',', $blacklist)));
				return function_exists($functionName) && !in_array($functionName, $blacklistFunctions);
			}

		}
		return function_exists($functionName);
	}
}
