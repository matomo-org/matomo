<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation\tests\System;

use Piwik\Config;
use Piwik\Tests\Framework\Constraint\HttpResponseText;
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

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $testingEnvironment = new \Piwik\Tests\Framework\TestingEnvironmentVariables();
        $testingEnvironment->configFileLocal = PIWIK_INCLUDE_PATH . '/plugins/Installation/tests/resources/config.ini.php';
        $testingEnvironment->save();
    }

    public function test_shouldReturnHttp500_IfWrongDbInfo()
    {
        $this->assertResponseCode(500, $this->getUrl());
    }

    public function test_shouldReturnValidApiResponse_IfWrongDbInfo_formatXML()
    {
        $http = new HttpResponseText('');
        $response = $http->getResponse($this->getUrl());

        $response = str_replace("\n", "", $response);

        $this->assertStringStartsWith('<?xml version="1.0" encoding="utf-8" ?><result>	<error message=', $response);
        $this->assertContains('Access denied', $response);
        $this->assertStringEndsWith('</result>', $response);
    }

    public function test_shouldReturnValidApiResponse_IfWrongDbInfo_formatJSON()
    {
        $http = new HttpResponseText('');
        $response = $http->getResponse($this->getUrl() . '&format=json');

        $response = str_replace("\n", "", $response);

        $this->assertStringStartsWith('{"result":"error","message":"', $response);
        $this->assertContains('Access denied', $response);
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

}