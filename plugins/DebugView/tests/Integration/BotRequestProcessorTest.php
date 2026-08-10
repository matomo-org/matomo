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
use Piwik\Plugins\DebugView\Tracker\BotRequestProcessor;
use Piwik\Plugins\DebugView\Tracker\RequestCapture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;

/**
 * @group DebugView
 * @group DebugViewBotRequestProcessorTest
 * @group Plugins
 */
class BotRequestProcessorTest extends IntegrationTestCase
{
    private const GOOGLEBOT_UA = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    /**
     * @var BotRequestProcessor
     */
    private $processor;

    /**
     * @var DebugRequests
     */
    private $model;

    /**
     * @var string|null
     */
    private $previousUserAgent;

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
        $this->processor = new BotRequestProcessor(new RequestCapture($this->model));

        // the tracker's bot routing and the captured default parameters both
        // read the user agent of the incoming HTTP request
        $this->previousUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $_SERVER['HTTP_USER_AGENT'] = self::GOOGLEBOT_UA;
    }

    public function tearDown(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = $this->previousUserAgent;

        parent::tearDown();
    }

    public function testHandleRequestStoresNothingWithoutTheDebugFlagEvenWhenArmed()
    {
        $this->model->markSiteActive(1);

        $request = new Request(['idsite' => 1, 'rec' => 1, 'url' => 'http://example.org/']);
        $wasHandled = $this->processor->handleRequest($request);

        $this->assertFalse($wasHandled);
        $this->assertSame([], $this->model->getForSite(1, 0));
    }

    public function testHandleRequestStoresNothingWhenNotArmed()
    {
        $request = new Request(['idsite' => 1, 'rec' => 1, 'debug' => '1', 'url' => 'http://example.org/']);
        $wasHandled = $this->processor->handleRequest($request);

        $this->assertFalse($wasHandled);
        $this->assertSame([], $this->model->getForSite(1, 0));
    }

    public function testHandleRequestCapturesTheBotRequestButNeverCountsAsHandled()
    {
        $this->model->markSiteActive(1);

        $request = new Request([
            'idsite' => 1,
            'rec' => 1,
            'debug' => '1',
            'url' => 'http://example.org/robots-view',
            'action_name' => 'Bot Crawl',
        ]);
        $wasHandled = $this->processor->handleRequest($request);

        // false: capturing debug data must not trigger archive invalidation
        $this->assertFalse($wasHandled);

        $rows = $this->model->getForSite(1, 0);
        $this->assertCount(1, $rows);
        // bot requests record no visit or action
        $this->assertNull($rows[0]['idvisit']);
        $this->assertNull($rows[0]['idlink_va']);

        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $this->assertSame('Bot Crawl', $decoded['query']['action_name']);
        $this->assertSame(['name' => 'Googlebot'], $decoded['bot']);
        $this->assertNull($decoded['actionType']);
    }

    public function testGetBotNameNamesTheDetectedBot()
    {
        $request = new Request(['idsite' => 1, 'rec' => 1]);

        $this->assertSame('Googlebot', $this->processor->getBotName($request));
    }

    public function testGetBotNameIsEmptyForARegularBrowserUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] =
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/124.0.0.0 Safari/537.36';

        $request = new Request(['idsite' => 1, 'rec' => 1]);

        $this->assertSame('', $this->processor->getBotName($request));
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
