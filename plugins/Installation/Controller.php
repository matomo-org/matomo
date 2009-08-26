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
 * 
 * @package Piwik_Installation
 */
class Piwik_Installation_Controller extends Piwik_Controller
{
	// public so plugins can add/delete installation steps
	public $steps = array(
			'welcome'				=> 'Installation_Welcome',
			'systemCheck'			=> 'Installation_SystemCheck',
			'databaseSetup'			=> 'Installation_MysqlSetup',
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
		$this->session = new Zend_Session_Namespace('Installation');
		if(!isset($this->session->currentStepDone)) 
		{
			$this->session->currentStepDone = '';
		}

		Piwik_PostEvent('InstallationController.construct', $this);
	}
	
	public function getInstallationSteps()
	{
		return $this->steps;
	}
	
	function getDefaultAction()
	{
		$steps = array_keys($this->steps);
		return $steps[0];
	}
	
	function welcome()
	{
		Piwik_Login_Controller::clearSession();

		$view = new Piwik_Installation_View(
						$this->pathView . 'welcome.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );
		$view->showNextStep = true;
		$this->session->currentStepDone = __FUNCTION__;		
		echo $view->render();
	}
	
	function systemCheck()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		
		$view = new Piwik_Installation_View(
						$this->pathView . 'systemCheck.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );
		
		$view->infos = $this->getSystemInformation();
		$view->helpMessages = array(
			'zlib'           => 'Installation_SystemCheckZlibHelp',
			'SPL'            => 'Installation_SystemCheckSplHelp',
			'set_time_limit' => 'Installation_SystemCheckTimeLimitHelp',
			'mail'           => 'Installation_SystemCheckMailHelp',
		);

		$view->problemWithSomeDirectories = (false !== array_search(false, $view->infos['directories']));
		
		$view->showNextStep = !$view->problemWithSomeDirectories 
							&& $view->infos['phpVersion_ok']
							&& $view->infos['pdo_ok']
							&& $view->infos['pdo_mysql_ok']
							&& !count($view->infos['missing_extensions'])
						;

		$this->session->currentStepDone = __FUNCTION__;

		echo $view->render();
	}
	
	
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
			$dbInfos = array(
				'host' 			=> $form->getSubmitValue('host'),
				'username' 		=> $form->getSubmitValue('username'),
				'password' 		=> $form->getSubmitValue('password'),
				'dbname' 		=> $form->getSubmitValue('dbname'),
				'tables_prefix' => $form->getSubmitValue('tables_prefix'),
				'adapter' 		=> Zend_Registry::get('config')->database->adapter,
				'port'			=> Zend_Registry::get('config')->database->port,
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
				} catch (Zend_Db_Adapter_Exception $e) {
					// database not found, we try to create  it
					if(preg_match('/1049/', $e->getMessage() ))
					{
						$dbInfosConnectOnly = $dbInfos;
						$dbInfosConnectOnly['dbname'] = null;
						Piwik::createDatabaseObject($dbInfosConnectOnly);
						Piwik::createDatabase($dbInfos['dbname']);
						$this->session->databaseCreated = true;
					}
				}
				
				$mysqlVersion = Piwik::getMysqlVersion();
				$minimumMysqlVersion = Zend_Registry::get('config')->General->minimum_mysql_version;
				if(version_compare($mysqlVersion, $minimumMysqlVersion) === -1) 
				{
					throw new Exception(Piwik_TranslateException('Installation_ExceptionMySqlVersion', array($mysqlVersion, $minimumMysqlVersion)));
				}
				
				$this->session->db_infos = $dbInfos;
				$this->redirectToNextStep( __FUNCTION__ );
			} catch(Exception $e) {
				$view->errorMessage = $e->getMessage();
			}
		}
		$view->addForm($form);
		
		$view->infos = $this->getSystemInformation();
			
		echo $view->render();
	}
	
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
			$view->tablesInstalled = implode(', ', $tablesInstalled);
			$view->someTablesInstalled = true;
			
			$minimumCountPiwikTables = 12;
			$baseTablesInstalled = preg_grep('/archive_numeric|archive_blob/', $tablesInstalled, PREG_GREP_INVERT);
			if(count($baseTablesInstalled) >= $minimumCountPiwikTables )
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
		
		if(isset($this->session->databaseCreated)
			&& $this->session->databaseCreated === true)
		{
			$view->databaseName = $this->session->db_infos['dbname'];
			$view->databaseCreated = true;
			unset($this->session->databaseCreated);
		}
		
		$this->session->currentStepDone = __FUNCTION__;
		echo $view->render();
	}
	
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
					Piwik::sendHttpRequest($url, $timeout = 2);
				} catch(Exception $e) {
					// e.g., disable_functions = fsockopen; allow_url_open = Off
				}
			}
			$this->redirectToNextStep( __FUNCTION__ );
		}
		$view->addForm($form);
			
		echo $view->render();
	}
	
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
		
		if($form->validate())
		{
			$name = urlencode($form->getSubmitValue('siteName'));
			$url = urlencode($form->getSubmitValue('url'));
			
			$this->initObjectsToCallAPI();
						
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
	
	public function finished()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'finished.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );
		$this->writeConfigFileFromSession();

		$this->session->currentStepDone = __FUNCTION__;		
		$view->showNextStep = false;
		
		@Zend_Session::destroy(true);
		echo $view->render();
	}
	
	protected function initObjectsToCallAPI()
	{
		// connect to the database using the DB infos currently in the session
		$this->createDbFromSessionInformation();

		Piwik::createAccessObject();
		Piwik::setUserIsSuperUser();
		Piwik::createLogObject();
	}
	
	protected function writeConfigFileFromSession()
	{
		if(!isset($this->session->superuser_infos)
			|| !isset($this->session->db_infos))
		{
			return;
		}
		$config = Zend_Registry::get('config');
		$config->superuser = $this->session->superuser_infos;
		$config->database = $this->session->db_infos;
	}
	
	/**
	 * The previous step is valid if it is either 
	 * - any step before (OK to go back)
	 * - the current step (case when validating a form)
	 */
	protected function checkPreviousStepIsValid( $currentStep )
	{
		$error = false;
		
		// first we make sure that the config file is not present, ie. Installation state is expected
		try {
			$config = new Piwik_Config();
			$config->init();
			$error = true;
		} catch(Exception $e) {
		}
		
		if(empty($this->session->currentStepDone))
		{
			$error = true;
		}
		else
		{
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
			$message = Piwik_Translate('Installation_ErrorInvalidState', 
						array( '<br /><b>',
								'</b>', 
								'<a href=\''.Piwik_Url::getCurrentUrlWithoutFileName().'\'>',
								'</a>')
					);
			Piwik::exitWithErrorMessage( $message );
		}		
	}

	protected function redirectToNextStep($currentStep)
	{
		$steps = array_keys($this->steps);
		$this->session->currentStepDone = $currentStep;
		$nextStep = $steps[1 + array_search($currentStep, $steps)];
		Piwik::redirectToModule('Installation' , $nextStep);
	}
	
	protected function createDbFromSessionInformation()
	{
		$dbInfos = $this->session->db_infos;
		Zend_Registry::get('config')->disableSavingConfigurationFileUpdates();
		Zend_Registry::get('config')->database = $dbInfos;
		Piwik::createDatabaseObject($dbInfos);
	}
	
	protected function getSystemInformation()
	{
		$minimumPhpVersion = Zend_Registry::get('config')->General->minimum_php_version;
		$minimumMemoryLimit = Zend_Registry::get('config')->General->minimum_memory_limit;
		
		$infos = array();
	
		$infos['directories'] = Piwik::checkDirectoriesWritable();
		$infos['phpVersion_minimum'] = $minimumPhpVersion;
		$infos['phpVersion'] = phpversion();
		$infos['phpVersion_ok'] = version_compare( $minimumPhpVersion, $infos['phpVersion']) === -1;

		// critical errors
		$extensions = @get_loaded_extensions();
		$needed_extensions = array(
			'zlib',
			'SPL',
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
		if (in_array('PDO', $extensions))
		{
			$infos['pdo_ok'] = true;
		}

		$infos['pdo_mysql_ok'] = false;
		if (in_array('pdo_mysql', $extensions))
		{
			$infos['pdo_mysql_ok'] = true;
		}

		// warnings
		$needed_functions = array(
			'set_time_limit',
			'mail',
		);
		$infos['needed_functions'] = $needed_functions;
		$infos['missing_functions'] = array();
		foreach($needed_functions as $needed_function)
		{
			if(!function_exists($needed_function))
			{
				$infos['missing_functions'][] = $needed_function;
			}
		}

		$infos['openurl'] = Piwik::getTransportMethod();

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

		$infos['isWindows'] = substr(PHP_OS, 0, 3) == 'WIN';
		
		return $infos;
	}
	
	protected function skipThisStep( $step )
	{
		if(isset($this->session->skipThisStep[$step])
			&& $this->session->skipThisStep[$step])
		{
			$this->redirectToNextStep($step);
		}
	}

	public function saveLanguage()
	{
		$language = Piwik_Common::getRequestVar('language');
		Piwik_LanguagesManager_API::setLanguageForSession($language);
		Piwik_Url::redirectToReferer();
	}
}
