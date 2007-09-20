<?php
require_once "View.php";
require_once "Installation/View.php";
class Piwik_Installation_Controller extends Piwik_Controller
{
	protected $steps = array(
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
		session_start();
		if(!isset($_SESSION['currentStepDone'])) 
		{
			$_SESSION['currentStepDone'] = '';
		}
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
		$view = new Piwik_Install_View(
						$this->pathView . 'welcome.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$view->showNextStep = true;
		
		$_SESSION['currentStepDone'] = __FUNCTION__;		
		echo $view->render();
	}
	
	function systemCheck()
	{
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		$view = new Piwik_Install_View(
						$this->pathView . 'systemCheck.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		
		$view->infos = $this->getSystemInformation();
		$view->problemWithSomeDirectories = (false !== array_search(false, $view->infos['directories']));
		
		$pathToMainPiwik = Piwik::getPathToPiwikRoot();
		$view->basePath = $pathToMainPiwik; 
		
		$view->showNextStep = !$view->problemWithSomeDirectories 
							&& $view->infos['phpVersion_ok']
							&& $view->infos['pdo_mysql_ok']
							&& $view->infos['phpXml_ok'];
		$_SESSION['currentStepDone'] = __FUNCTION__;		

		echo $view->render();
	}
	
	
	function databaseSetup()
	{
		$view = new Piwik_Install_View(
						$this->pathView . 'databaseSetup.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->checkPreviousStepIsValid( __FUNCTION__ );
					
		$view->showNextStep = false;
		require_once "FormDatabaseSetup.php";
		$form = new Piwik_Installation_FormDatabaseSetup;
		
		if($form->validate())
		{
//			var_dump(Zend_Registry::get('config')->database);
			$dbInfos = array(
				'host' 			=> $form->getSubmitValue('host'),
				'username' 		=> $form->getSubmitValue('username'),
				'password' 		=> $form->getSubmitValue('password'),
				'dbname' 		=> $form->getSubmitValue('dbname'),
				'tables_prefix' => $form->getSubmitValue('tables_prefix'),
				'adapter' 		=> Zend_Registry::get('config')->database->adapter,
			);
			
			// we test the DB connection with these settings
			try{ 
				Piwik::createDatabaseObject($dbInfos);
				$_SESSION['db_infos'] = $dbInfos;
			
				$_SESSION['currentStepDone'] = __FUNCTION__;
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
		$view = new Piwik_Install_View(
						$this->pathView . 'tablesCreation.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->checkPreviousStepIsValid( __FUNCTION__ );
					
		$this->createDbFromSessionInformation();
		
		if(Piwik_Common::getRequestVar('deleteTables', 0, 'int') == 1)
		{
			Piwik::dropTables();
			$view->existingTablesDeleted = true;
		}
		
		$tablesInstalled = Piwik::getTablesInstalled();
		$tablesToInstall = Piwik::getTablesNames();
		
		if(count($tablesInstalled) > 0)
		{
			$view->someTablesInstalled = true;
			$view->tablesInstalled = implode(", ", $tablesInstalled);
		}
		else
		{
			Piwik::createTables();
			
			$view->tablesCreated = true;
			$view->showNextStep = true;
		}
		
		$_SESSION['currentStepDone'] = __FUNCTION__;
		echo $view->render();
	}
	
	function generalSetup()
	{		
		$view = new Piwik_Install_View(
						$this->pathView . 'generalSetup.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		
		require_once "FormGeneralSetup.php";
		$form = new Piwik_Installation_FormGeneralSetup;
		
		if($form->validate())
		{			
			$superUserInfos = array(
				'login' 		=> $form->getSubmitValue('login'),
				'password' 		=> $form->getSubmitValue('password'),
				'email' 		=> $form->getSubmitValue('email'),
			);
			
			$_SESSION['superuser_infos'] = $superUserInfos;
			$_SESSION['currentStepDone'] = __FUNCTION__;
			$this->redirectToNextStep( __FUNCTION__ );
		}
		$view->addForm($form);
			
		echo $view->render();
	}
	
	public function firstWebsiteSetup()
	{
				
		$view = new Piwik_Install_View(
						$this->pathView . 'firstWebsiteSetup.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		
		require_once "FormFirstWebsiteSetup.php";
		$form = new Piwik_Installation_FormFirstWebsiteSetup;
		
		if( !isset($_SESSION['generalSetupSuccessMessage']))
		{
			$view->displayGeneralSetupSuccess = true;
			$_SESSION['generalSetupSuccessMessage'] = true;
		}
		
		if($form->validate())
		{
			// we setup the superuser login & password in the config that will be checked by the
			// API authentication process
			Zend_Registry::get('config')->superuser = $_SESSION['superuser_infos'];
			
			$name = urlencode($form->getSubmitValue('name'));
			$url = urlencode($form->getSubmitValue('url'));
			
			// connect to the database using the DB infos currently in the session
			$this->createDbFromSessionInformation();

			// create the fake access to grant super user privilege
			Zend_Registry::set('access', new Piwik_FakeAccess_SetSuperUser);
			
			// we need to create the logs otherwise the API request throws an exception
			Piwik::createLogObject();
			
			require_once "API/Request.php";
			$request = new Piwik_API_Request("
							method=SitesManager.addSite
							&name=$name
							&urls=$url
							&format=original
						");
						
			try {
				$result = $request->process();
				$_SESSION['site_idSite'] = $result;
				$_SESSION['site_name'] = $name;
				$_SESSION['site_url'] = $url;
				$_SESSION['currentStepDone'] = __FUNCTION__;
		
				$this->redirectToNextStep( __FUNCTION__ );
			} catch(Exception $e) {
				$view->errorMessage = $e->getMessage();
			}

		}
		$view->addForm($form);
		
		echo $view->render();
	}
	protected function writeConfigFileFromSession()
	{
		$configFile = "; file automatically generated during the piwik installation process\n";
		
		// super user information
		$configFile .= "[superuser]\n";
		foreach( $_SESSION['superuser_infos'] as $key => $value)
		{
			$configFile .= "$key = $value\n";
		}
		$configFile .= "\n";
		
		// database information
		$configFile .= "[database]\n";
		foreach($_SESSION['db_infos'] as  $key => $value)
		{
			$configFile .= "$key = $value\n";
		}
		
		file_put_contents(Piwik_Config::getDefaultUserConfigPath(), $configFile);
	}
	public function displayJavascriptCode()
	{
		$view = new Piwik_Install_View(
						$this->pathView . 'displayJavascriptCode.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$this->checkPreviousStepIsValid( __FUNCTION__ );
		
		if( !isset($_SESSION['firstWebsiteSetupSuccessMessage']))
		{
			$view->displayfirstWebsiteSetupSuccess = true;
			$_SESSION['firstWebsiteSetupSuccessMessage'] = true;
		}
		
		$this->writeConfigFileFromSession();
		
		$view->websiteName = $_SESSION['site_name'];
		
		$jsTag = file_get_contents( PIWIK_INCLUDE_PATH . "/modules/LogStats/javascriptTag.tpl");
		$jsTag = nl2br(htmlentities($jsTag));
		$jsTag = str_replace('{$actionName}', "''", $jsTag);
		$jsTag = str_replace('{$idSite}', $_SESSION['site_idSite'], $jsTag);
		$jsTag = str_replace('{$piwikUrl}', Piwik_Url::getCurrentUrlWithoutFileName(), $jsTag);
		
		$view->javascriptTag = $jsTag;
		$view->showNextStep = true;
		
		$_SESSION['currentStepDone'] = __FUNCTION__;
		echo $view->render();
	}
	
	public function finished()
	{
		$view = new Piwik_Install_View(
						$this->pathView . 'finished.tpl', 
						$this->getInstallationSteps(),
						__FUNCTION__
					);
		$view->showNextStep = true;
		
		$_SESSION['currentStepDone'] = __FUNCTION__;		
		$view->showNextStep = false;
		
//		session_destroy();
		echo $view->render();
		
		// cron tab help
		// javascript reminder
		// giving good names to pages
	}
	
	
	
	/**
	 * The previous step is valid if it is either 
	 * - any step before (OK to go back)
	 * - the current step (case when validating a form)
	 */
	function checkPreviousStepIsValid( $currentStep )
	{
		// the currentStep
		$currentStepId = array_search($currentStep, $this->steps);
		
		// the step before
		$previousStepId = array_search($_SESSION['currentStepDone'], $this->steps);
		
		// not OK if currentStepId > previous+1
		if( $currentStepId > $previousStepId + 1 )
		{
			//print("$currentStepId > $previousStepId");
			print("Error: you can only go back during the installation process. 
				<br>Make sure your cookies are enabled and go back 
				<a href='".Piwik_Url::getCurrentUrlWithoutFileName()."'>to the first page of the installation</a>.");
			exit;
		}		
	}
	
	protected function redirectToNextStep($currentStep)
	{
		$nextStep = $this->steps[1 + array_search($currentStep, $this->steps)];
		Piwik::redirectToModule('Installation' , $nextStep);
	}
	
	protected function createDbFromSessionInformation()
	{
		$dbInfos = $_SESSION['db_infos'];		
		Zend_Registry::get('config')->database = $dbInfos;
		Piwik::createDatabaseObject($dbInfos);
	}
	
	
	
	protected function checkDirectoriesWritable()
	{
		$directoriesToCheck = array(
			'/config',
			'/tmp',
			'/tmp/templates_c',
			'/tmp/configs',
			'/tmp/cache',
		); 
		
		$resultCheck = array();
		
		foreach($directoriesToCheck as $name)
		{
			$directoryToCheck = PIWIK_INCLUDE_PATH . $name;
			
			$resultCheck[$name] = false;
			
			if(!is_writable($directoryToCheck))
			{			
				Piwik::mkdir($directoryToCheck);
			}
			
			if(is_writable($directoryToCheck))
			{
				$resultCheck[$name] = true;
			}
		}
		
		return $resultCheck;
	}
	
	protected function getSystemInformation()
	{
		$minimumPhpVersion = Zend_Registry::get('config')->General->minimumPhpVersion;
		$minimumMemoryLimit = Zend_Registry::get('config')->General->minimumMemoryLimit;
		
		$infos = array();
	
		// directory to write
		$infos['directories'] = $this->checkDirectoriesWritable();
		
		// php version
		$infos['phpVersion_minimum'] = $minimumPhpVersion;
		$infos['phpVersion'] = phpversion();
		$infos['phpVersion_ok'] = version_compare( $minimumPhpVersion, $infos['phpVersion']) === -1;
		
		$extensions = @get_loaded_extensions();
		
		$infos['pdo_mysql_ok'] = false;
		// Mysql + version
		if (in_array('pdo_mysql', $extensions))  
		{
		    $infos['pdo_mysql_ok'] = true;
		    //TODO add the mysql version report and check mini 4.1
//			$infos['pdo_mysql_version'] = getMysqlVersion();
		}
		
		// server version
		$infos['serverVersion'] = addslashes($_SERVER['SERVER_SOFTWARE']);
	
		// server os (linux)
		$infos['serverOs'] = @php_uname();
		
		// server time
		$infos['serverTime'] = date('H:i:s');
				
		if(function_exists( 'set_time_limit'))
		{
			$infos['setTimeLimit_ok'] = true;
		}
	
		$infos['phpXml_ok'] = false;
		if(function_exists( 'utf8_encode') 
			&& function_exists( 'utf8_decode'))
		{
			$infos['phpXml_ok'] = true;
		}
		
		$infos['mail_ok'] = false;
		if(function_exists('mail'))
		{
			$infos['mail_ok'] = true;
		}
		
		//Registre global
		$infos['registerGlobals_ok'] = ini_get('register_globals') == 0;
		
		$raised = Piwik::raiseMemoryLimitIfNecessary();
		if(	$memoryValue = Piwik::getMemoryLimitValue() )
		{
			$infos['memoryCurrent'] = $memoryValue."M";
			$infos['memoryMinimum'] = $minimumMemoryLimit;
			$infos['memory_ok'] = $memoryValue >= $minimumMemoryLimit;
		}
				/*
		// server uptime from mysql uptime
		$res = query('SHOW STATUS');
		if($res)
		{
			while ($row = mysql_fetch_array($res)) 
			{
			   $serverStatus[$row[0]] = $row[1];
			}
	
			$infos['server_uptime'] = date("r",time() - $serverStatus['Uptime']); 		
		}*/
		
		return $infos;
	}
}
class Piwik_FakeAccess_SetSuperUser {
	function checkUserIsSuperUser()
	{
		return true;
	}
	function loadAccess() {}
}
