<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Version;

/**
 * @group System
 */
class EnvironmentValidationTest extends SystemTestCase
{
    public function getEntryPointsToTest()
    {
        return array(
            array('tracker'),
            array('web'),
            array('console'),
            array('archive_web')
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        $testingEnvironment = new \Piwik\Tests\Framework\TestingEnvironmentVariables();
        $testingEnvironment->configFileGlobal = null;
        $testingEnvironment->configFileLocal = null;
        $testingEnvironment->configFileCommon = null;
        $testingEnvironment->loadRealTranslations = true;
        $testingEnvironment->save();
    }

    /**
     * @dataProvider getEntryPointsToTest
     */
    public function testNoGlobalConfigFileTriggersError($entryPoint)
    {
        $this->simulateAbsentConfigFile('global.ini.php');

        $output = $this->triggerPiwikFrom($entryPoint);

        $this->assertOutputContainsConfigFileMissingError('global.ini.php', $output);
    }

    public function testNoLocalConfigFileTriggersErrorInTracker()
    {
        $this->simulateAbsentConfigFile('config.ini.php');

        $output = $this->triggerPiwikFrom('tracker');
        self::assertStringContainsString('As Matomo is not installed yet, the Tracking API cannot proceed and will exit without error.', $output);
    }

    public function testNoLocalConfigFileTriggersErrorInConsole()
    {
        $this->simulateAbsentConfigFile('config.ini.php');

        $output = $this->triggerPiwikFrom('console');
        $this->assertOutputContainsConfigFileMissingError('config.ini.php', $output);
    }

    public function testNoLocalConfigFileStartsInstallationPiwikAccessedThroughWeb()
    {
        $this->simulateAbsentConfigFile('config.ini.php');

        $output = $this->triggerPiwikFrom('web');
        $this->assertInstallationProcessStarted($output);
    }

    public function getEntryPointsAndConfigFilesToTest()
    {
        return array(
            array('global.ini.php', 'tracker'),
            array('global.ini.php', 'web'),
            array('global.ini.php', 'console'),
            array('global.ini.php', 'archive_web'),

            array('config.ini.php', 'tracker'),
            array('config.ini.php', 'web'),
            array('config.ini.php', 'console'),
            array('config.ini.php', 'archive_web'),

            array('common.config.ini.php', 'tracker'),
            array('common.config.ini.php', 'web'),
            array('common.config.ini.php', 'console'),
            array('common.config.ini.php', 'archive_web'),
        );
    }

    /**
     * @dataProvider getEntryPointsAndConfigFilesToTest
     */
    public function testBadConfigFileTriggersError($configFile, $entryPoint)
    {
        $this->simulateBadConfigFile($configFile);

        $output = $this->triggerPiwikFrom($entryPoint);

        $this->assertOutputContainsBadConfigFileError($output);
    }

    /**
     * @dataProvider getEntryPointsToTest
     */
    public function testBadDomainSpecificLocalConfigFileTriggersError($entryPoint)
    {
        $this->simulateHost('piwik.kobra.org');

        $configFile = 'piwik.kobra.org.config.ini.php';
        $this->simulateBadConfigFile($configFile);

        $output = $this->triggerPiwikFrom($entryPoint);
        $this->assertOutputContainsBadConfigFileError($output);
    }

    private function assertOutputContainsConfigFileMissingError($fileName, $output)
    {
        $this->assertRegExp(
            "/.*The configuration file \\{.*\\/" . preg_quote($fileName) . "\\} has not been found or could not be read\\..*/",
            (string) $output,
            "Output did not contain the expected exception for $fileName --- Output was --- $output"
        );
    }

    private function assertOutputContainsBadConfigFileError($output)
    {
        $this->assertRegExp("/Unable to read INI file \\{.*\\/matomo.php\\}:/", $output);
        $this->assertRegExp("/Your host may have disabled parse_ini_file\\(\\)/", $output);
    }

    private function assertInstallationProcessStarted($output)
    {
        self::assertStringContainsString('<title>Matomo ' . Version::VERSION . ' &rsaquo; Installation</title>', $output);
    }

    private function simulateAbsentConfigFile($fileName)
    {
        $testingEnvironment = new \Piwik\Tests\Framework\TestingEnvironmentVariables();

        if ($fileName == 'global.ini.php') {
            $testingEnvironment->configFileGlobal = PIWIK_INCLUDE_PATH . '/tmp/nonexistant/global.ini.php';
        } elseif ($fileName == 'common.config.ini.php') {
            $testingEnvironment->configFileCommon = PIWIK_INCLUDE_PATH . '/tmp/nonexistant/common.config.ini.php';
        } else {
            $testingEnvironment->configFileLocal = PIWIK_INCLUDE_PATH . '/tmp/nonexistant/' . $fileName;
        }

        $testingEnvironment->save();
    }

    private function simulateBadConfigFile($fileName)
    {
        $testingEnvironment = new \Piwik\Tests\Framework\TestingEnvironmentVariables();

        if ($fileName == 'global.ini.php') {
            $testingEnvironment->configFileGlobal = PIWIK_INCLUDE_PATH . '/matomo.php';
        } elseif ($fileName == 'common.config.ini.php') {
            $testingEnvironment->configFileCommon = PIWIK_INCLUDE_PATH . '/matomo.php';
        } else {
            $testingEnvironment->configFileLocal = PIWIK_INCLUDE_PATH . '/matomo.php';
        }

        $testingEnvironment->save();
    }

    private function simulateHost($host)
    {
        $testingEnvironment = new \Piwik\Tests\Framework\TestingEnvironmentVariables();
        $testingEnvironment->hostOverride = $host;
        $testingEnvironment->save();
    }

    private function triggerPiwikFrom($entryPoint)
    {
        if ($entryPoint == 'tracker') {
            return $this->sendRequestToTracker();
        } elseif ($entryPoint == 'web') {
            return $this->sendRequestToWeb();
        } elseif ($entryPoint == 'console') {
            return $this->startConsoleProcess();
        } elseif ($entryPoint == 'archive_web') {
            return $this->sendArchiveWebRequest();
        } else {
            throw new \Exception("Don't know how to access '$entryPoint'.");
        }
    }

    private function sendRequestToTracker()
    {
        list($response, $info) = $this->curl(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/matomo.php?idsite=1&rec=1&action_name=something');

        // Check Tracker requests return 200
        $this->assertEquals(200, $info["http_code"], 'Ok response');

        return $response;
    }

    private function sendRequestToWeb()
    {
        list($response, $info) = $this->curl(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php');
        return $response;
    }

    private function sendArchiveWebRequest()
    {
        list($response, $info) = $this->curl(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/archive.php?token_auth=' . Fixture::getTokenAuth());
        return $response;
    }

    private function startConsoleProcess()
    {
        $pathToProxyConsole = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/console';
        return shell_exec("php '$pathToProxyConsole' list 2>&1");
    }

    private function curl($url)
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('Curl is not installed');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response = substr($response, $headerSize);

        $responseInfo = curl_getinfo($ch);

        curl_close($ch);

        return array($response, $responseInfo);
    }
}
