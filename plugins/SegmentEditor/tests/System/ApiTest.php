<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\System;

use Piwik\Config;
use Piwik\Http;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class ApiTest extends SystemTestCase
{
    public function test_segmentHashWorkflow_whenSegmentIsCrazyEncoded()
    {
        $segment = 'pageUrl=@%252F1';

        Fixture::createWebsite('2020-03-03 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->save();

        $url = Fixture::getTestRootUrl() . '?' . http_build_query([
            'module' => 'API',
            'method' => 'SegmentEditor.add',
            'name' => 'test segment',
            'definition' => $segment,
            'idSite' => 1,
            'autoArchive' => 1,
            'enabledAllUsers' => 1,
            'format' => 'json',
            'token_auth' => Fixture::getTokenAuth(),
        ]);
        self::assertStringContainsString(urlencode($segment), $url);

        Http::sendHttpRequest($url, 10);

        $segments = SegmentEditorApi::getInstance()->getAll();
        $segmentDefinitionHash = end($segments);
        $segmentDefinitionHash = $segmentDefinitionHash['hash'];

        $url = Fixture::getTestRootUrl() . '?' . http_build_query([
            'module' => 'API',
            'method' => 'ExamplePlugin.getSegmentHash',
            'segment' => $segment,
            'idSite' => 1,
            'format' => 'json',
            'token_auth' => Fixture::getTokenAuth(),
        ]);

        $segmentApiHash = Http::sendHttpRequest($url, 10);
        $segmentApiHash = json_decode($segmentApiHash, true);
        $segmentApiHash = $segmentApiHash['value'];

        $this->assertEquals($segmentApiHash, $segmentDefinitionHash);
    }

    /**
    * @dataProvider definitionsDataProvider
    */
    public function test_generatedSegmentHash($definition)
    {
        Fixture::createWebsite('2020-03-03 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->save();

        $idSegment = SegmentEditorApi::getInstance()->add('test segment', $definition, 1, 1, 1);
        $segment = SegmentEditorApi::getInstance()->get($idSegment);

        $hash = $segment['hash'];
        $generatedHash = md5(urldecode($segment['definition']));

        $this->assertEquals($generatedHash, $hash);
    }

    public function definitionsDataProvider()
    {
        return [
            ['pageUrl=@%252F1'],
            ['actions>=1'],
            ['operatingSystemName==Ubuntu;browserName==Firefox'],
            ['pageUrl==https%253A%252F%252Fmatomo.org%252Fpricing%252F'],
            ['visitIp>=80.229.0.0;visitIp<=80.229.255.255'],
        ];
    }
}
