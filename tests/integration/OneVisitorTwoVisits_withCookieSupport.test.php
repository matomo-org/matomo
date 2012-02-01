<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/OneVisitorTwoVisits.test.php';

/**
 * Same as OneVisitorTwoVisits.test.php, but with cookie support, which incurs some slight changes 
 * in the reporting data (more accurate unique visitor count, better referer tracking for goals, etc.)
 */
class Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport extends Test_Piwik_Integration_OneVisitorTwoVisits
{
	public function getApiToTest()
	{
		$apiToCall = array(
			'VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'UserSettings',
			'UserCountry', 'Referers', 'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome',
			'Actions', 'Live.getLastVisitsDetails');
	
		return array(
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime))
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}

	public function getOutputPrefix()
	{
		return 'OneVisitorTwoVisits_withCookieSupport';
	}

	protected function trackVisits()
	{
        $t = $this->getTracker($this->idSite, $this->dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        $t->DEBUG_APPEND_URL = '&forceUseThirdPartyCookie=1';
		$this->trackVisitsImpl($t);
	}
}
