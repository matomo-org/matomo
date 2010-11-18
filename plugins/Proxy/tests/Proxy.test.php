<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

class Test_Piwik_Proxy extends UnitTestCase
{
    public function test_isAcceptableRemoteUrl()
    {
	Piwik::createConfigObject();

        $data = array(
            // piwik white list (and used in homepage)
            'http://piwik.org/' => array(true, true),

            'http://piwik.org' => array(true, false),
            'http://qa.piwik.org/' => array(true, false),
            'http://forum.piwik.org/' => array(true, false),
            'http://dev.piwik.org/' => array(true, false),
            'http://demo.piwik.org/' => array(true, false),

            // not in the piwik white list
            'http://www.piwik.org/' => array(false, false),
            'https://piwik.org/' => array(false, false),

            // plugin author_homepage (must be an exact match)
            'http://clearcode.cc' => array(false, false),
            'http://clearcode.cc/' => array(false, true),
        );

	foreach($data as $url => $expected)
        {
            $this->assertEqual(Piwik_Proxy_Controller::isPiwikUrl($url), $expected[0], $url);
            $this->assertEqual(Piwik_Proxy_Controller::isAcceptableRemoteUrl($url), $expected[1], $url);
        }
    }
}

