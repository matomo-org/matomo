<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation\tests\System;

use Piwik\Http;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Installation
 * @group APITest
 * @group Plugins
 */
class APITest extends SystemTestCase
{
    /**
     * @var Fixture
     */
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $testingEnvironment = new \Piwik\Tests\Framework\TestingEnvironmentVariables();
        $testingEnvironment->configFileLocal = PIWIK_INCLUDE_PATH . '/plugins/Installation/tests/resources/config.ini.php';
        $testingEnvironment->save();
    }

    public function test_shouldReturnHttp500_IfWrongDbInfo()
    {
        $response = $this->sendHttpRequest($this->getUrl());
        $this->assertEquals(500, $response['status']);
    }

    public function test_shouldReturnValidApiResponse_IfWrongDbInfo_formatXML()
    {
        $response = $this->sendHttpRequest($this->getUrl());

        $data = str_replace("\n", "", $response['data']);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="utf-8" ?><result>	<error message=', $data);
        self::assertStringContainsString('Database access denied', $data);
        $this->assertStringEndsWith('</result>', $data);
    }

    public function test_shouldReturnValidApiResponse_IfWrongDbInfo_formatJSON()
    {
        $response = $this->sendHttpRequest($this->getUrl() . '&format=json');

        $data = str_replace("\n", "", $response['data']);

        $this->assertStringStartsWith('{"result":"error","message":"', $data);
        self::assertStringContainsString('Database access denied', $data);
    }

    public function test_shouldReturnEmptyResultWhenNotInstalledAndDispatchIsDisabled()
    {
        $url = Fixture::getTestRootUrl() . 'nodispatchnotinstalled.php';
        $response = $this->sendHttpRequest($url);
        $this->assertSame('', $response['data']);
    }

    private function getUrl()
    {
        return Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=API&method=API.getPiwikVersion';
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    protected function sendHttpRequest($url)
    {
        return Http::sendHttpRequest($url, 10, null, null, 0, false, false, true);
    }
}
