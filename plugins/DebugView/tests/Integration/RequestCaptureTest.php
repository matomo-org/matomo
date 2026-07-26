<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Plugins\DebugView\Model\DebugRequests;
use Piwik\Plugins\DebugView\Tracker\RequestCapture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;

/**
 * @group DebugView
 * @group DebugViewRequestCaptureTest
 * @group Plugins
 */
class RequestCaptureTest extends IntegrationTestCase
{
    /**
     * @var RequestCapture
     */
    private $capture;

    /**
     * @var DebugRequests
     */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2020-01-01 00:00:00');
        }

        $this->model = new DebugRequests(new RawRequestLog());
        $this->capture = new RequestCapture($this->model);
    }

    public function testShouldCaptureIsFalseForARequestWithoutAnyParameters()
    {
        $this->model->markSiteActive(1);

        $this->assertFalse($this->capture->shouldCapture(new Request([])));
    }

    public function testShouldCaptureIsFalseWithoutTheDebugFlagEvenWhenArmed()
    {
        $this->model->markSiteActive(1);

        $request = new Request(['idsite' => 1, 'rec' => 1, 'url' => 'http://example.org/']);

        $this->assertFalse($this->capture->shouldCapture($request));
    }

    public function testShouldCaptureIsFalseWhenTheSiteIsNotArmed()
    {
        $request = new Request(['idsite' => 1, 'rec' => 1, 'debug' => '1', 'url' => 'http://example.org/']);

        $this->assertFalse($this->capture->shouldCapture($request));
    }

    public function testShouldCaptureIsTrueWhenArmedAndFlagged()
    {
        $this->model->markSiteActive(1);

        $request = new Request(['idsite' => 1, 'rec' => 1, 'debug' => '1', 'url' => 'http://example.org/']);

        $this->assertTrue($this->capture->shouldCapture($request));
    }

    public function testCaptureStoresTheRedactedParametersWithDefaultsAndOther()
    {
        $this->model->markSiteActive(1);

        $request = new Request([
            'idsite' => 1,
            'rec' => 1,
            'debug' => '1',
            'url' => 'http://example.org/page',
            'token_auth' => 'secret-token-value',
        ]);

        $this->capture->capture($request, 42, 7, \Piwik\Tracker\Action::TYPE_PAGE_URL);

        $rows = $this->model->getForSite(1, 0);
        $this->assertCount(1, $rows);
        $this->assertSame('42', (string) $rows[0]['idvisit']);
        $this->assertSame('7', (string) $rows[0]['idlink_va']);

        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $this->assertSame('__redacted__', $decoded['query']['token_auth']);
        $this->assertArrayHasKey('userAgent', $decoded['defaults']);
        $this->assertArrayHasKey('isAuthenticated', $decoded['other']);
        $this->assertSame(\Piwik\Tracker\Action::TYPE_PAGE_URL, $decoded['actionType']);
        $this->assertNull($decoded['bot']);
    }

    public function testCaptureStoresTheBotGroupForBotRequests()
    {
        $this->model->markSiteActive(1);

        $request = new Request(['idsite' => 1, 'rec' => 1, 'debug' => '1', 'url' => 'http://example.org/']);

        $this->capture->capture($request, null, null, null, ['name' => 'Googlebot']);

        $rows = $this->model->getForSite(1, 0);
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['idvisit']);
        $this->assertNull($rows[0]['idlink_va']);

        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $this->assertSame(['name' => 'Googlebot'], $decoded['bot']);
    }

    public function testGetDefaultParametersContainsThePassivelyReceivedData()
    {
        $previousUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $previousLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $_SERVER['HTTP_USER_AGENT'] = 'DebugView Test Agent';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9';

        try {
            $defaults = $this->capture->getDefaultParameters();
        } finally {
            $_SERVER['HTTP_USER_AGENT'] = $previousUserAgent;
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $previousLanguage;
        }

        $this->assertSame('DebugView Test Agent', $defaults['userAgent']);
        $this->assertStringContainsString('de', $defaults['browserLanguage']);
        $this->assertIsArray($defaults['clientHints']);
        $this->assertEqualsWithDelta(time(), $defaults['serverTimeReceived'], 10);
    }

    public function testGetOtherParametersReportsUnauthenticatedRequests()
    {
        $request = new Request(['idsite' => 1, 'rec' => 1]);

        $other = $this->capture->getOtherParameters($request);

        $this->assertSame(['isAuthenticated' => false], $other);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
