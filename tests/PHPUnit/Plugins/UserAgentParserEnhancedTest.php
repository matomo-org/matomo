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
        if($fixtures != $parsed) {
            $processed = Spyc::YAMLDump($parsed, false, $wordWrap = 0);
            $processedPath = $fixturesPath . '.new';
            file_put_contents($processedPath, $processed);
            $diffCommand = "diff";
//            $diffCommand = "meld";
            echo shell_exec("{$diffCommand} $fixturesPath $processedPath");

            echo "\nThe processed data was stored in: $processedPath ".
                "\n $ cp $processedPath $fixturesPath ".
                "\n to copy the file over if it is valid.";

            $this->assertTrue(false);

        }
        $this->assertTrue(true);
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
            'device'         => array(
                'type'       => $deviceName,
                'brand'      => $userAgentParserEnhanced->getBrand(),
                'model'      => $userAgentParserEnhanced->getModel(),
            ),
            'os_family'      => $osFamily !== false ? $osFamily : 'Unknown',
            'browser_family' => $browserFamily !== false ? $browserFamily : 'Unknown',
        );
        return $processed;
    }

}
