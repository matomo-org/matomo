<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * test the Yearly metadata API response, 
 * with no visits, with custom response language 
 */
class Test_Piwik_Integration_ApiGetReportMetadata_Year extends Test_Integration_Facade
{
	protected $idSite = null;
	protected $dateTime = null;

	public function getApiToTest()
	{
		$apiToCall = array('API.getProcessedReport', 
		//FIXME TODO re-enable me!
//							'API.getReportMetadata', 
							'LanguagesManager.getTranslationsForLanguage', 
							'LanguagesManager.getAvailableLanguageNames',
							'SitesManager.getJavascriptTag');
		return array(
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime, 'periods' => 'year',
									'language' => 'fr')),
		);
	}

	public function getOutputPrefix()
	{
		return 'apiGetReportMetadata_year';
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function setUp()
	{
		parent::setUp();

		$this->dateTime = '2009-01-04 00:11:42';
		$this->idSite = $this->createWebsite($this->dateTime);
	}

	protected function trackVisits()
	{
		// empty
	}
}
