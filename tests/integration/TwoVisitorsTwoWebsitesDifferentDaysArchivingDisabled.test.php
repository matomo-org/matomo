<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitorsTwoWebsitesDifferentDays.test.php';

/**
 * TODO
 */
class Test_Piwik_Integration_TwoVisitorsTwoWebsitesDifferentDaysArchivingDisabled extends Test_Piwik_Integration_TwoVisitorsTwoWebsitesDifferentDays
{
	public function __construct($title = '')
	{
		parent::__construct($title);
		$this->allowConversions = true;
	}

	public function getApiToTest()
	{
		$periods = array('day', 'week', 'month', 'year');
		
		return array(
			// disable archiving & check that there is no archive data
			array('VisitsSummary.get', array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => $periods,
											 'disableArchiving' => true, 'testSuffix' => '_disabledBefore')),
			
			// re-enable archiving & check the output
			array('VisitsSummary.get', array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => $periods,
											 'disableArchiving' => false, 'testSuffix' => '_enabled')),
			
			// diable archiving again & check the output
			array('VisitsSummary.get', array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => $periods,
											 'disableArchiving' => true, 'testSuffix' => '_disabledAfter')),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'TwoVisitors_twoWebsites_differentDays_ArchivingDisabled';
	}
}
