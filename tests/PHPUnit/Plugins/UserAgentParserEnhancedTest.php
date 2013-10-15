<?php

require_once 'DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php';

class UserAgentParserEnhancedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Plugins
     * @dataProvider getUserAgents_asParsed
     */
    public function testParse($expected)
    {
        $ua = $expected['user_agent'];

        $userAgentParserEnhanced = new UserAgentParserEnhanced($ua);
        $userAgentParserEnhanced->parse();

        $processed =  array(
            'user_agent' => $userAgentParserEnhanced->getUserAgent(),
            'os' => array(
                'name' => $userAgentParserEnhanced->getOs('name'),
                'short_name' => $userAgentParserEnhanced->getOs('short_name'),
                'version' => $userAgentParserEnhanced->getOs('version'),
            ),
            'browser' => array(
                'name' => $userAgentParserEnhanced->getBrowser('name'),
                'short_name' => $userAgentParserEnhanced->getBrowser('short_name'),
                'version' => $userAgentParserEnhanced->getBrowser('version'),
            ),
            'device' => $userAgentParserEnhanced->getDevice(),
            'brand' => $userAgentParserEnhanced->getBrand(),
            'model' => $userAgentParserEnhanced->getModel(),
            'os_family' => $userAgentParserEnhanced->getOsFamily($userAgentParserEnhanced->getOs('name')),
            'browser_family' => $userAgentParserEnhanced->getBrowserFamily($userAgentParserEnhanced->getBrowser('name')),
        );

        $this->assertEquals($expected, $processed);
    }

    public function getUserAgents_asParsed()
    {
        $expected = array();

        $fixtures = Spyc::YAMLLoad(dirname(__FILE__) . '/../Fixtures/userAgentParserEnhancedFixtures.yml');
        foreach ($fixtures as $fixtureData) {
            $expected[] = array($fixtureData);
        }

        return $expected;
    }
}
