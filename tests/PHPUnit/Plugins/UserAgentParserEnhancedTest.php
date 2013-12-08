<?php

require_once 'DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php';

class UserAgentParserEnhancedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Plugins
     */
    public function testParse()
    {
        $fixturesPath = realpath(dirname(__FILE__) . '/../Fixtures/userAgentParserEnhancedFixtures.yml');
        $fixtures = Spyc::YAMLLoad($fixturesPath);
        foreach ($fixtures as $fixtureData) {
            $ua = $fixtureData['user_agent'];
            $uaInfo = $this->getInfoFromUserAgent($ua);
            $parsed[] = $uaInfo;
        }
        $processed = Spyc::YAMLDump($parsed, false, $wordWrap = 0);
        $processedPath = $fixturesPath . '.new';
        file_put_contents($processedPath, $processed);
        if($fixtures != $parsed) {
            echo "\nThe processed data was stored in: $processedPath ".
                "\n $ cp $processedPath $fixturesPath ".
                "\n to copy the file over if it is valid.";
            echo shell_exec("diff $processedPath $fixturesPath ");

            $this->assertTrue(false);

        }
    }

    private function getInfoFromUserAgent($ua)
    {
        $userAgentParserEnhanced = new UserAgentParserEnhanced($ua);
        $userAgentParserEnhanced->parse();

        $osFamily = $userAgentParserEnhanced->getOsFamily($userAgentParserEnhanced->getOs('name'));
        $browserFamily = $userAgentParserEnhanced->getBrowserFamily($userAgentParserEnhanced->getBrowser('name'));
        $device = $userAgentParserEnhanced->getDevice();

        $deviceName = $device === '' ? '' : UserAgentParserEnhanced::$deviceTypes[$device];
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
            'device'         => $device,
//            'device_name'    => $deviceName,
            'brand'          => $userAgentParserEnhanced->getBrand(),
            'model'          => $userAgentParserEnhanced->getModel(),
            'os_family'      => $osFamily !== false ? $osFamily : 'Unknown',
            'browser_family' => $browserFamily !== false ? $browserFamily : 'Unknown',
        );
        return $processed;
    }

}
