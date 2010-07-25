<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once "API/DocumentationGenerator.php";

class Test_Piwik_API_DocumentationGenerator extends UnitTestCase
{
	function test_callableApiMethods_doNotFail()
	{
		Piwik::createConfigObject();
		Zend_Registry::get('config')->setTestEnvironment();	
		Piwik::createLogObject();
		Piwik::createAccessObject();
		Piwik::createDatabaseObject();
		Piwik::setUserIsSuperUser();
    	Piwik_Translate::getInstance()->loadEnglishTranslation();
		$pluginsManager = Piwik_PluginsManager::getInstance();
		$pluginsManager->loadPlugins( Zend_Registry::get('config')->Plugins->Plugins->toArray() );
		$apiGenerator = new Piwik_API_DocumentationGenerator_CallAllMethods();
		
		$requestUrls = $apiGenerator->getAllRequestsWithParameters();
		$this->assertTrue(count($requestUrls) > 20);
		foreach($requestUrls as $url)
		{
			$call = new Piwik_API_Request($url);
			$output = $call->process();
//			var_dump($url);
//			var_dump($output);
			$this->assertTrue(!empty($output));
		}
    	Piwik_Translate::getInstance()->unloadEnglishTranslation();
		$this->pass();
	}
}

class Piwik_API_DocumentationGenerator_CallAllMethods extends Piwik_API_DocumentationGenerator
{
	function getAllRequestsWithParameters()
	{
		$requestUrls = array();
		$parametersToSet = array(
			'idSite' 	=> '1',
			'period' 	=> 'week',
			'date'		=> 'today',
			'expanded'  => '1',
		);
		
		foreach(Piwik_API_Proxy::getInstance()->getMetadata() as $class => $info)
		{
			$moduleName = Piwik_API_Proxy::getInstance()->getModuleNameFromClassName($class);
			foreach($info as $methodName => $infoMethod)
			{
				$params = $this->getParametersString($class, $methodName);
				$exampleUrl = $this->getExampleUrl($class, $methodName, $parametersToSet);
				if($exampleUrl !== false)
				{
					$requestUrls[] = $exampleUrl;
				}
			}
		}
		return $requestUrls;
	}
}
