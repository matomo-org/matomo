<?php

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/UserAgentParserEnhanced/UserAgentParserEnhanced.php';

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
            $uaInfo = UserAgentParserEnhanced::getInfoFromUserAgent($ua);
            $parsed[] = $uaInfo;
        }
        if($fixtures != $parsed) {
            $processed = Spyc::YAMLDump($parsed, false, $wordWrap = 0);
            $processedPath = $fixturesPath . '.new';
            file_put_contents($processedPath, $processed);
            $diffCommand = "diff -a1 -b1";
            $command = "{$diffCommand} $fixturesPath $processedPath";
            echo $command . "\n";
            echo shell_exec($command);

            echo "\nThe processed data was stored in: $processedPath ".
                "\n $ cp $processedPath $fixturesPath ".
                "\n to copy the file over if it is valid.";

            $this->assertTrue(false);

        }
        $this->assertTrue(true);
    }

    /**
     * @group Plugins
     * @dataProvider getAllOs
     */
    public function testOSInGroup($os)
    {

        foreach (UserAgentParserEnhanced::$osFamilies as $family => $labels) {
            if (in_array($os, $labels)) {
                return true;
            }
        }

        $this->fail('Operating System not in a group');
    }

    public function getAllOs()
    {
        $allOs = array_values(UserAgentParserEnhanced::$osShorts);
        $allOs = array_map(function($os){ return array($os); }, $allOs);
        return $allOs;
    }
}
