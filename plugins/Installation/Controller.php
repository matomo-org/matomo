<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
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
			'welcome',
			'systemCheck',
			'databaseSetup',
			'tablesCreation',
			'generalSetup',
			'firstWebsiteSetup',
			'displayJavascriptCode',
			'finished'
		);
		
	protected $pathView = 'Installation/templates/';
	
	public function __construct()
	{
		$session = new Zend_Session_Namespace("Installation");
		if(!isset($session->currentStepDone)) 
		{
			$session->currentStepDone = '';
		}
		
		Piwik_PostEvent('InstallationController.construct', $this);
	}
	
	public function getInstallationSteps()
	{
		return $this->steps;
	}
	
	function getDefaultAction()
	{
		return $this->steps[0];
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
		$session = new Zend_Session_Namespace("Installation");
		$session->currentStepDone = __FUNCTION__;		
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
		$view->problemWithSomeDirectories = (false !== array_search(false, $view->infos['directories']));
		
		$view->showNextStep = !$view->problemWithSomeDirectories 
							&& $view->infos['phpVersion_ok']
							&& $view->infos['pdo_ok']
							&& $view->infos['pdo_mysql_ok']

						;
		$session = new Zend_Session_Namespace("Installation");
		$session->currentStepDone = __FUNCTION__;

		echo $view->render();
	}
	
	
	function databaseSetup()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		
		// case the user hits the back button
		$session = new Zend_Session_Namespace("Installation");
		$session->skipThisStep = array(
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

			if($dbInfos['host'][0] == '/')
			{
				$dbInfos['unix_socket'] = $dbInfos['host'];
				$dbInfos['host'] = '';
			}
			else if(($portIndex = strpos($dbInfos['host'],':')) !== false)
			{
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
						$session->databaseCreated = true;
					}
				}
				
				$mysqlVersion = Piwik::getMysqlVersion();
				$minimumMysqlVersion = Zend_Registry::get('config')->General->minimum_mysql_version;
				if(version_compare($mysqlVersion, $minimumMysqlVersion) === -1) 
				{
					throw new Exception(vsprintf("Your MySQL version is %s but Piwik requires at least %s.", array($mysqlVersion, $minimumMysqlVersion)));
				}
				
				$session->db_infos = $dbInfos;
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
		$session = new Zend_Session_Namespace("Installation");
		$this->skipThisStep( __FUNCTION__ );
		$this->createDbFromSessionInformation();
		
		if(Piwik_Common::getRequestVar('deleteTables', 0, 'int') == 1)
		{
			Piwik::dropTables();
			$view->existingTablesDeleted = true;
			
			// when the user decides to drop the tables then we dont skip the next steps anymore
			$session->skipThisStep['firstWebsiteSetup'] = false;
			$session->skipThisStep['displayJavascriptCode'] = false;
		}
		
		$tablesInstalled = Piwik::getTablesInstalled();
		$tablesToInstall = Piwik::getTablesNames();
		$view->tablesInstalled = '';
		if(count($tablesInstalled) > 0)
		{
			$view->tablesInstalled = implode(", ", $tablesInstalled);
			$view->someTablesInstalled = true;
			
			$minimumCountPiwikTables = 14;
			if(count($tablesInstalled) >= $minimumCountPiwikTables )
			{
				$view->showReuseExistingTables = true;
				// when the user reuses the same tables we skip the website creation step
				$session->skipThisStep['firstWebsiteSetup'] = true;
	                        $session->skipThisStep['displayJavascriptCode'] = true;
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
		
		if(isset($session->databaseCreated)
			&& $session->databaseCreated === true)
		{
			$view->databaseName = $session->db_infos['dbname'];
			$view->databaseCreated = true;
			unset($session->databaseCreated);
		}
		
		$session->currentStepDone = __FUNCTION__;
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
		$session = new Zend_Session_Namespace("Installation");

		$form = new Piwik_Installation_FormGeneralSetup();
		
		if($form->validate())
		{			
			$superUserInfos = array(
				'login' 		=> $form->getSubmitValue('login'),
				'password' 		=> md5( $form->getSubmitValue('password') ),
				'email' 		=> $form->getSubmitValue('email'),
			);
			
			$session->superuser_infos = $superUserInfos;
			
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
				$url = $host . "?" . http_build_query($params, '', '&');
				Piwik::sendHttpRequest($url, $timeout = 2);
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
		$session = new Zend_Session_Namespace("Installation");
		if( !isset($session->generalSetupSuccessMessage))
		{
			$view->displayGeneralSetupSuccess = true;
			$session->generalSetupSuccessMessage = true;
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
				$session->site_idSite = $result;
				$session->site_name = $name;
				$session->site_url = $url;
				
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
		$session = new Zend_Session_Namespace("Installation");
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		
		$view = new Piwik_Installation_View(
						$this->pathView . 'displayJavascriptCode.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );
		
		if( !isset($session->firstWebsiteSetupSuccessMessage))
		{
			$view->displayfirstWebsiteSetupSuccess = true;
			$session->firstWebsiteSetupSuccessMessage = true;
		}
		
		
		$view->websiteName = urldecode($session->site_name);
		
		$jsTag = Piwik::getJavascriptCode($session->site_idSite, Piwik_Url::getCurrentUrlWithoutFileName());
		
		$view->javascriptTag = $jsTag;
		$view->showNextStep = true;
		
		$session->currentStepDone = __FUNCTION__;
		echo $view->render();
	}
	
	public function finished()
	{
		$session = new Zend_Session_Namespace("Installation");
		$this->checkPreviousStepIsValid( __FUNCTION__ );

		$view = new Piwik_Installation_View(
						$this->pathView . 'finished.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->skipThisStep( __FUNCTION__ );
		$this->writeConfigFileFromSession();

		$session->currentStepDone = __FUNCTION__;		
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
		$session = new Zend_Session_Namespace("Installation");
		if(!isset($session->superuser_infos)
			|| !isset($session->db_infos))
		{
			return;
		}
		$config = Zend_Registry::get('config');
		$config->superuser = $session->superuser_infos;
		$config->database = $session->db_infos;
	}
	
	/**
	 * The previous step is valid if it is either 
	 * - any step before (OK to go back)
	 * - the current step (case when validating a form)
	 */
	protected function checkPreviousStepIsValid( $currentStep )
	{
		$session = new Zend_Session_Namespace("Installation");
		$error = false;
		
		// first we make sure that the config file is not present, ie. Installation state is expected
		try {
			$config = new Piwik_Config();
			$config->init();
			$error = true;
		} catch(Exception $e) {
		}
		
		if(empty($session->currentStepDone))
		{
			$error = true;
		}
		else
		{
			// the currentStep
			$currentStepId = array_search($currentStep, $this->steps);
			
			// the step before
			$previousStepId = array_search($session->currentStepDone, $this->steps);
	
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
		$session = new Zend_Session_Namespace("Installation");
		$session->currentStepDone = $currentStep;
		$nextStep = $this->steps[1 + array_search($currentStep, $this->steps)];
		Piwik::redirectToModule('Installation' , $nextStep);
	}
	
	protected function createDbFromSessionInformation()
	{
		$session = new Zend_Session_Namespace("Installation");
		$dbInfos = $session->db_infos;
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
		
		$extensions = @get_loaded_extensions();
		
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

		$infos['setTimeLimit_ok'] = false;
		if(function_exists( 'set_time_limit'))
		{
			$infos['setTimeLimit_ok'] = true;
		}

		$infos['mail_ok'] = false;
		if(function_exists('mail'))
		{
			$infos['mail_ok'] = true;
		}
		
		$infos['registerGlobals_ok'] = ini_get('register_globals') == 0;
		$infos['memoryMinimum'] = $minimumMemoryLimit;
		
		$infos['memory_ok'] = true;
		// on windows the ini_get is not working?
		$infos['memoryCurrent'] = '?M';

		$raised = Piwik::raiseMemoryLimitIfNecessary();
		if(	$memoryValue = Piwik::getMemoryLimitValue() )
		{
			$infos['memoryCurrent'] = $memoryValue."M";
			$infos['memory_ok'] = $memoryValue >= $minimumMemoryLimit;
		}
		
		return $infos;
	}
	
	protected function skipThisStep( $step )
	{
		$session = new Zend_Session_Namespace("Installation");
		if(isset($session->skipThisStep[$step])
			&& $session->skipThisStep[$step])
		{
			$this->redirectToNextStep($step);
		}
	}
}
