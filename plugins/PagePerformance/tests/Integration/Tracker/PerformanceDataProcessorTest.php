<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance\tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\PagePerformance\Tracker\PerformanceDataProcessor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;


/**
 * @group PagePerformance
 * @group Plugins
 */
class PerformanceDataProcessorTest extends IntegrationTestCase
{
    /**
     * @var PerformanceDataProcessor
     */
    private $requestProcessor;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestProcessor = new PerformanceDataProcessor();

        Fixture::createWebsite('2014-01-01 02:03:04');
    }

    public function test_shouldUpdatePerformanceTimingsInOngoingEventRequest()
    {
        $tracker = Fixture::getTracker(1, Date::now()->toString('Y-m-d H:i:s'));
        $tracker->setUrl('http://example.org/test');
        Fixture::checkResponse($tracker->doTrackPageView('My Page'));

        $idPageView = $tracker->idPageview;

        $this->checkActionHasTimings($idPageView);

        $tracker->setPerformanceTimings(12, 77, 412, 1055, 333, 66);
        Fixture::checkResponse($tracker->doTrackEvent('cat', 'act'));

        $this->checkActionHasTimings($idPageView, 12, 77, 412, 1055, 333, 66);
    }

    public function test_shouldUpdatePerformanceTimingsInOngoingPingRequest()
    {
        $tracker = Fixture::getTracker(1, Date::now()->toString('Y-m-d H:i:s'));
        $tracker->setUrl('http://example.org/test');
        Fixture::checkResponse($tracker->doTrackPageView('My Page'));

        $idPageView = $tracker->idPageview;

        $this->checkActionHasTimings($idPageView);

        $tracker->setPerformanceTimings(5, 66, 445, 1025, 12, 111);
        Fixture::checkResponse($tracker->doPing());

        $this->checkActionHasTimings($idPageView, 5, 66, 445, 1025, 12, 111);
    }

    public function test_shouldNotUpdatePerformanceTimingsInOngoingPageViewRequest()
    {
        $tracker = Fixture::getTracker(1, Date::now()->toString('Y-m-d H:i:s'));
        $tracker->setUrl('http://example.org/test');
        Fixture::checkResponse($tracker->doTrackPageView('My Page'));

        $idPageView = $tracker->idPageview;

        $this->checkActionHasTimings($idPageView);

        $tracker->setPerformanceTimings(0, 66, 445, 1025, 12, 111);
        $tracker->setUrl('http://example.org/test2');
        Fixture::checkResponse($tracker->doTrackPageView('Another Page'));

        $this->checkActionHasTimings($idPageView);
        $this->checkActionHasTimings($tracker->idPageview, 0, 66, 445, 1025, 12, 111);
    }

    public function test_shouldNotUseObviouslyTooHighNumbers()
    {
        $tracker = Fixture::getTracker(1, Date::now()->toString('Y-m-d H:i:s'));
        $tracker->setUrl('http://example.org/test');
        Fixture::checkResponse($tracker->doTrackPageView('My Page'));

        $idPageView = $tracker->idPageview;

        $this->checkActionHasTimings($idPageView);

        $tracker->setPerformanceTimings(6525265942, 6525265942, 6525265942, 6525265942, 6525265942, 111);
        $tracker->setUrl('http://example.org/test2');
        Fixture::checkResponse($tracker->doTrackPageView('Another Page'));

        $this->checkActionHasTimings($idPageView);
        $this->checkActionHasTimings($tracker->idPageview, null, null, null, null, null, 111);
    }

    protected function checkActionHasTimings($pageViewId, $network = null, $server = null, $transfer = null, $domProcessing = null, $domCompletion = null, $onload = null)
    {
        $result = Db::fetchRow(
            sprintf('SELECT time_network, time_server, time_transfer, time_dom_processing, time_dom_completion, time_on_load 
                      FROM %1$s LEFT JOIN %2$s ON idaction_url = idaction WHERE idpageview = ? AND %2$s.type = 1',
                Common::prefixTable('log_link_visit_action'),
                Common::prefixTable('log_action')
        ), $pageViewId);

        $this->assertEquals([
            'time_network' => $network,
            'time_server' => $server,
            'time_transfer' => $transfer,
            'time_dom_processing' => $domProcessing,
            'time_dom_completion' => $domCompletion,
            'time_on_load' => $onload
        ], $result);
    }
}
