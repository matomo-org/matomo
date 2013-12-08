<?php

require_once 'DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php';

class UserAgentParserEnhancedTest extends PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $fixtures = Spyc::YAMLLoad(dirname(__FILE__) . '/../Fixtures/userAgentParserEnhancedFixtures.yml');
        foreach ($fixtures as $fixtureData) {
            $ua = $fixtureData['user_agent'];
            $uaInfo = $this->getInfoFromUserAgent($ua);
            $parsed[] = $uaInfo;
        }
        $this->assertEquals($fixtures, $parsed);
    }

    private function getInfoFromUserAgent($ua)
    {
        $userAgentParserEnhanced = new UserAgentParserEnhanced($ua);
        $userAgentParserEnhanced->parse();

        $osFamily = $userAgentParserEnhanced->getOsFamily($userAgentParserEnhanced->getOs('name'));
        $browserFamily = $userAgentParserEnhanced->getBrowserFamily($userAgentParserEnhanced->getBrowser('name'));
        $processed = array(
            'user_agent'     => $userAgentParserEnhanced->getUserAgent(),
            'os'             => array(
                'name'       => $userAgentParserEnhanced->getOs('name'),
                'short_name' => $userAgentParserEnhanced->getOs('short_name'),
                'version'    => $userAgentParserEnhanced->getOs('version'),
            ),
            'browser'        => array(
                'name'       => $userAgentParserEnhanced->getBrowser('name'),
                'short_name' => $userAgentParserEnhanced->getBrowser('short_name'),
                'version'    => $userAgentParserEnhanced->getBrowser('version'),
            ),
            'device'         => $userAgentParserEnhanced->getDevice(),
            'brand'          => $userAgentParserEnhanced->getBrand(),
            'model'          => $userAgentParserEnhanced->getModel(),
            'os_family'      => $osFamily !== false ? $osFamily : 'Unknown',
            'browser_family' => $browserFamily !== false ? $browserFamily : 'Unknown',
        );
        return $processed;
    }

}
