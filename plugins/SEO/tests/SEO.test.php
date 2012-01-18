<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

Mock::generate('Piwik_Access');

class Test_SEO extends UnitTestCase
{
	function setUp()
	{
		parent::setUp();

		$pseudoMockAccess = new MockPiwik_Access();
		$pseudoMockAccess->setReturnValue('isSuperUser', true);
		Zend_Registry::set('access', $pseudoMockAccess);

		Piwik::createConfigObject();

		$user_agents = array(
			'Mozilla/6.0 (Macintosh; I; Intel Mac OS X 11_7_9; de-LI; rv:1.9b4) Gecko/2012010317 Firefox/10.0a4',
			'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/18.6.872.0 Safari/535.2 UNTRUSTED/1.0 3gpp-gba UNTRUSTED/1.0',
			'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
		);

		$_SERVER['HTTP_USER_AGENT'] = $user_agents[mt_rand(0, count($user_agents) - 1)];
	}

	// tell us when the API is broken
	function test_API()
	{
		$dataTable = Piwik_SEO_API::getInstance()->getRank('http://forum.piwik.org/');
		$renderer = Piwik_DataTable_Renderer::factory('php');
		$renderer->setSerialize(false);
		$ranks = $renderer->render($dataTable);
		foreach ($ranks as $rank)
		{
			$this->assertTrue(!empty($rank['rank']), $rank['id'] . ' expected non-zero rank, got [' . $rank['rank'] . ']');
		}
	}
}
