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
use Piwik\Plugins\DebugView\Tracker\RawRequestProcessor;
use Piwik\Plugins\DebugView\Tracker\RequestCapture;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * @group DebugView
 * @group DebugViewRawRequestProcessorTest
 * @group Plugins
 */
class RawRequestProcessorTest extends IntegrationTestCase
{
    /**
     * @var RawRequestProcessor
     */
    private $processor;

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
        $this->processor = new RawRequestProcessor(new RequestCapture($this->model));
    }

    public function testRecordLogsStoresNothingWithoutDebugFlagEvenWhenArmed()
    {
        $this->model->markSiteActive(1);

        $request = new Request(['idsite' => 1, 'rec' => 1, 'url' => 'http://example.org/']);
        $this->processor->recordLogs(new VisitProperties(), $request);

        $this->assertSame([], $this->model->getForSite(1, 0));
    }

    public function testRecordLogsStoresTheRequestWhenArmedAndFlagged()
    {
        $this->model->markSiteActive(1);

        $request = new Request([
            'idsite' => 1,
            'rec' => 1,
            'debug' => '1',
            'url' => 'http://example.org/page',
            'action_name' => 'Processor Test',
            'token_auth' => 'secret-token-value',
        ]);
        $visitProperties = new VisitProperties();
        $visitProperties->setProperty('idvisit', 42);

        $this->processor->recordLogs($visitProperties, $request);

        $rows = $this->model->getForSite(1, 0);
        $this->assertCount(1, $rows);
        $this->assertSame('42', (string) $rows[0]['idvisit']);

        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $this->assertSame('Processor Test', $decoded['query']['action_name']);
        $this->assertSame('__redacted__', $decoded['query']['token_auth']);
        $this->assertArrayHasKey('isAuthenticated', $decoded['other']);
        // the pageview Action re-derived via Action::factory provides the type
        $this->assertSame(\Piwik\Tracker\Action::TYPE_PAGE_URL, $decoded['actionType']);
        // the visit path never marks requests as bot requests
        $this->assertNull($decoded['bot']);
    }

    public function testRecordLogsStoresNothingWhenNotArmed()
    {
        $request = new Request([
            'idsite' => 1,
            'rec' => 1,
            'debug' => '1',
            'url' => 'http://example.org/page',
        ]);

        $this->processor->recordLogs(new VisitProperties(), $request);

        $this->assertSame([], $this->model->getForSite(1, 0));
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
