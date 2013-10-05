<?php

require_once 'DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php';

class UserAgentParserEnhancedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testParse($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }

    public function provider()
    {
        $providerData = array();

        $fixtures = Spyc::YAMLLoad(dirname(__FILE__) . '/../Fixtures/userAgentParserEnhancedFixtures.yml');
        foreach ($fixtures as $fixtureData) {
            $userAgentParserEnhanced = new UserAgentParserEnhanced($fixtureData['user_agent']);
            $userAgentParserEnhanced->parse();

            $providerData[] = array(
                $fixtureData,
                array(
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
                )
            );
        }

        return $providerData;
    }
}
