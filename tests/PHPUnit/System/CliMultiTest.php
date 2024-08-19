<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Archiver\Request;
use Piwik\CliMulti;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreConsole\FeatureFlags\CliMultiProcessSymfony;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\TestLogger;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Version;

/**
 * @group Core
 * @group CliMulti
 */
class CliMultiTest extends SystemTestCase
{
    /**
     * @var \Piwik\CliMulti
     */
    private $cliMulti;

    /**
     * @var string
     */
    private $authToken = '';

    /**
     * @var TestLogger
     */
    private $logger;

    /**
     * @var string[]
     */
    private $urls = [];

    /**
     * @var string[]
     */
    private $responses = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestLogger();
        $this->authToken = Fixture::getTokenAuth();

        $this->cliMulti = new CliMulti($this->logger);

        $this->urls = [
            'getAnswerToLife' => $this->completeUrl('?module=API&method=ExampleAPI.getAnswerToLife&format=JSON'),
            'getPiwikVersion' => $this->completeUrl('?module=API&method=API.getPiwikVersion&format=JSON'),
        ];

        $this->responses = [
            'getAnswerToLife' => '{"value":42}',
            'getPiwikVersion' => '{"value":"' . Version::VERSION . '"}'
        ];

        \Piwik\Common::$isCliMode = true;

        // deactivate symfony process usage by default
        // required as local instance could have activated the feature flag
        $this->cliMulti->supportsAsyncSymfony = false;
    }

    public function testRequestShouldNotFailAndReturnNoResponseIfNoUrlsAreGiven()
    {
        $response = $this->cliMulti->request(array());

        $this->assertEquals(array(), $response);
    }

    public function testRequestShouldFailIfUrlsIsNotAnArray()
    {
        $this->expectException('TypeError');
        $this->cliMulti->request('');
    }

    public function testRequestShouldReturnResultAsArrayIfOnlyOneUrlIsGiven()
    {
        $urls = $this->buildUrls('getAnswerToLife');

        $this->assertRequestReturnsValidResponses($urls, ['getAnswerToLife']);
        $this->assertDebugLogContainsRequestDetails($urls, 'syncCli');
    }

    public function testRequestShouldRunAsync()
    {
        $this->assertTrue($this->cliMulti->supportsAsync);
    }

    /**
     * @dataProvider getShouldDetectRunningAsyncUsingSymfonyData
     */
    public function testShouldDetectRunningAsyncUsingSymfonyIsSupported(
        bool $supportsAsync,
        bool $isFeatureFlagEnabled,
        bool $expectedResult
    ): void {
        $mockFeatureFlagManager = $this->createMock(FeatureFlagManager::class);
        $mockFeatureFlagManager
            ->method('isFeatureActive')
            ->with(CliMultiProcessSymfony::class)
            ->willReturn($isFeatureFlagEnabled);

        StaticContainer::getContainer()->set(FeatureFlagManager::class, $mockFeatureFlagManager);

        $cliMulti = new CliMulti();
        $cliMulti->supportsAsync = $supportsAsync;

        self::assertSame($expectedResult, $cliMulti->supportsAsyncSymfony());
    }

    public function getShouldDetectRunningAsyncUsingSymfonyData(): iterable
    {
        yield 'supportsAsync and feature flag disabled' => [false, false, false];
        yield 'supportsAsync enabled, feature flag disabled' => [true, false, false];
        yield 'supportsAsync disabled, feature flag enabled' => [false, true, false];
        yield 'supportsAsync and feature flag enabled' => [true, true, true];
    }


    public function testShouldNotAllowUsingSymfonyProcessIfFeatureFlagCheckThrows(): void
    {
        $cliMulti = new CliMulti();

        $mockFeatureFlagManager = $this->createMock(FeatureFlagManager::class);
        $mockFeatureFlagManager
            ->method('isFeatureActive')
            ->willThrowException(new \Exception());

        StaticContainer::getContainer()->set(FeatureFlagManager::class, $mockFeatureFlagManager);

        self::assertFalse($cliMulti->supportsAsyncSymfony());
    }

    public function testRequestShouldRequestAllUrlsIfMultipleUrlsAreGiven()
    {
        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife');

        $this->assertRequestReturnsValidResponses($urls, ['getPiwikVersion', 'getAnswerToLife']);
        $this->assertDebugLogContainsRequestDetails($urls, 'asyncCli');
    }

    public function testRequestShouldRequestAllUrlsIfMultipleUrlsAreGivenWithConcurrentRequestLimit()
    {
        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion');

        $this->cliMulti->setConcurrentProcessesLimit(1);
        $this->assertRequestReturnsValidResponses($urls, ['getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion']);
        $this->assertDebugLogContainsRequestDetails($urls, 'syncCli');
    }

    public function testRequestShouldRequestAllUrlsIfMultipleUrlsAreGivenWithHighConcurrentRequestLimit()
    {
        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion');

        $this->cliMulti->setConcurrentProcessesLimit(10);
        $this->assertRequestReturnsValidResponses($urls, ['getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion']);
        $this->assertDebugLogContainsRequestDetails($urls, 'asyncCli');
    }

    public function testRequestShouldReturnSameAmountOfResponsesIfSameUrlAppearsMultipleTimes()
    {
        $urls = $this->buildUrls('getAnswerToLife', 'getAnswerToLife', 'getPiwikVersion');

        $this->assertRequestReturnsValidResponses($urls, ['getAnswerToLife', 'getAnswerToLife', 'getPiwikVersion']);
    }

    public function testRequestShouldCleanupAllTempFilesOnceAllRequestsAreFinished()
    {
        $filesBefore = $this->getFilesInTmpFolder();

        $this->cliMulti->request($this->buildUrls('getAnswerToLife', 'getAnswerToLife'));

        $filesAfter = $this->getFilesInTmpFolder();

        $this->assertSame($filesAfter, $filesBefore, "Diff is :" . implode(", ", array_diff($filesAfter, $filesBefore)));
        $this->assertGreaterThan(1, $filesAfter);
    }

    public function testRequestShouldWorkInCaseItDoesNotRunFromCli()
    {
        $urls = $this->buildUrls('getAnswerToLife', 'getAnswerToLife');

        \Piwik\Common::$isCliMode = false;

        $this->assertRequestReturnsValidResponses($urls, ['getAnswerToLife', 'getAnswerToLife']);
        $this->assertDebugLogContainsRequestDetails($urls, 'http');
    }

    /**
     * This is a known issue, we do not get a content in case Piwik ends with an exit or redirect, but we have to make
     * sure we detect the request has finished though
     */
    public function testRequestShouldDetectFinishOfRequestIfNoParamsAreGiven()
    {
        $this->cliMulti->runAsSuperUser();
        $response = $this->cliMulti->request(array($this->completeUrl('')));
        self::assertStringContainsString('Error: no website was found', $response[0]);
    }

    public function testRequestShouldBeAbleToRenderARegularPageInPiwik()
    {
        Fixture::createWebsite('2014-01-01 00:00:00');

        $urls = array($this->completeUrl('/?module=Widgetize&idSite=1&period=day&date=today'));

        $response = $this->cliMulti->request($urls);

        $message = "Response was: " . substr(implode("\n\n", $response), 0, 4000);
        $this->assertTrue(false !== strpos($response[0], '<meta name="generator" content="Matomo - free/libre analytics platform"/>'), $message);
        $this->assertTrue(false !== strpos($response[0], 'Widgetize the full dashboard') . $message);
    }

    public function testShouldFallbackIfAsyncIsNotSupported()
    {
        $this->cliMulti->supportsAsync = false;

        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion');

        $this->assertRequestReturnsValidResponses($urls, ['getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion']);
        $this->assertDebugLogContainsRequestDetails($urls, 'http');
    }

    public function testShouldRunWithSymfonyProcessIfDetected(): void
    {
        $this->cliMulti->supportsAsyncSymfony = true;

        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion');

        $this->assertRequestReturnsValidResponses($urls, ['getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion']);
        $this->assertDebugLogContainsRequestDetails($urls, 'asyncCliSymfony');
    }

    public function testCleanupNotRemovedFilesShouldOnlyRemoveFilesIfTheyAreOlderThanOneWeek()
    {
        $timeOneWeekAgo = strtotime('-1 week');

        // make sure -1 week returns a timestamp one week ago
        $this->assertGreaterThan(604797, time() - $timeOneWeekAgo);
        $this->assertLessThan(604803, time() - $timeOneWeekAgo);

        $tmpDir = CliMulti::getTmpPath() . '/';
        touch($tmpDir . 'now.pid');
        touch($tmpDir . 'now.output');
        touch($tmpDir . 'toberemoved.pid', $timeOneWeekAgo - 10);
        touch($tmpDir . 'toberemoved.output', $timeOneWeekAgo - 10);
        touch($tmpDir . 'toBeNotRemoved.pid', $timeOneWeekAgo + 10);
        touch($tmpDir . 'toBeNotRemoved.output', $timeOneWeekAgo + 10);

        CliMulti::cleanupNotRemovedFiles();

        $this->assertFileExists($tmpDir . 'now.pid');
        $this->assertFileExists($tmpDir . 'now.output');
        $this->assertFileExists($tmpDir . 'toBeNotRemoved.output');
        $this->assertFileExists($tmpDir . 'toBeNotRemoved.output');

        $this->assertFileNotExists($tmpDir . 'toberemoved.output');
        $this->assertFileNotExists($tmpDir . 'toberemoved.output');
    }

    public function testShouldSupportRequestObjects()
    {
        $wasCalled = false;
        $request = new Request('url');
        $request->before(function () use (&$wasCalled) {
            $wasCalled = true;
        });

        $this->cliMulti->request(array($request));

        $this->assertTrue($wasCalled, 'The request "before" handler was not called');
    }

    private function assertDebugLogContainsRequestDetails(array $urls, string $method): void
    {
        foreach ($urls as $url) {
            if ('http' === $method) {
                $pattern = '/Execute HTTP API request.*' . $url . '/';
            } else {
                $pattern = '/climulti:request.*' . $url . '.*\[method = ' . $method . ']/';
            }

            $this->logger->hasDebugThatMatches($pattern);
        }
    }

    private function assertRequestReturnsValidResponses($urls, $expectedResponseIds)
    {
        $actualResponse = $this->cliMulti->request($urls);

        self::assertIsArray($actualResponse, '$actualResponse is not an array');
        $this->assertCount(count($expectedResponseIds), $actualResponse);

        $expected = array();
        foreach ($expectedResponseIds as $expectedResponseId) {
            $expected[] = $this->responses[$expectedResponseId];
        }

        $this->assertEquals($expected, $actualResponse);
    }

    private function buildUrls()
    {
        $urls = array();

        foreach (func_get_args() as $urlId) {
            $urls[] = $this->urls[$urlId];
        }

        return $urls;
    }

    private function completeUrl($query)
    {
        if (false === strpos($query, '?')) {
            $query .= '?';
        } else {
            $query .= '&';
        }

        return $query . 'testmode=1&token_auth=' . $this->authToken;
    }

    private function getFilesInTmpFolder()
    {
        $dir = PIWIK_INCLUDE_PATH . '/tmp';

        $files = \_glob($dir . "/*");
        $subFiles = \_glob($dir . "/*/*");

        $files = array_merge($files, $subFiles);

        return $files;
    }
}
