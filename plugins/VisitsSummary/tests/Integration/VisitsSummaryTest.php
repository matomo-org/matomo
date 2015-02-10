<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary\tests\Integration;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Plugins\VisitsSummary\VisitsSummary;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group VisitsSummary
 * @group VisitsSummaryTest
 * @group Plugins
 */
class VisitsSummaryTest extends IntegrationTestCase
{
    /**
     * @var VisitsSummary
     */
    private $plugin;

    protected $date = '2014-04-04';
    private $column = 'nb_users';

    public function setUp()
    {
        parent::setUp();
        $this->plugin = new VisitsSummary();

        $this->setSuperUser();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 00:00:00');
    }

    public function tearDown()
    {
        // clean up your test here if needed
        $tables = ArchiveTableCreator::getTablesArchivesInstalled();
        if (!empty($tables)) {
            Db::dropTables($tables);
        }
        parent::tearDown();
    }

    public function test_enrichProcessedReportIfVisitsSummaryGet_shouldNotRemoveUsers_IfSomeWereTracked()
    {
        $this->trackPageviewsWithUsers();

        $response = $this->requestProcessedGetReport();

        $this->assertUsersNotRemovedFromProcessedReport($response, $expectedUsers = 2.0);
    }

    public function test_enrichProcessedReportIfVisitsSummaryGet_shouldNotRemoveUsers_IfNoneWereTrackedThatDay_ButThatMonth()
    {
        $this->date = '2014-04-04';
        $this->trackPageviewsWithUsers();

        $this->date = '2014-04-05';
        $this->trackPageviewsWithoutUsers();

        $response = $this->requestProcessedGetReport();

        $this->assertUsersNotRemovedFromProcessedReport($response, $expectedUsers = 0);
    }

    public function test_isUsedInAtLeastOneSite_shouldRemoveUsers_IfNoneWereTracked()
    {
        $this->trackPageviewsWithoutUsers();

        $response = $this->requestProcessedGetReport();

        $this->assertUsersRemovedFromProcessedReport($response);
    }

    private function assertUsersNotRemovedFromProcessedReport($response, $numUsers)
    {
        $table = $response['reportData'];
        $this->assertSame(array($numUsers), $table->getColumn($this->column));
        $this->assertEquals(array(3), $table->getColumn('nb_visits'));
        $this->assertNotEmpty($response['metadata']['metrics'][$this->column]);
        $this->assertNotEmpty($response['metadata']['metricsDocumentation'][$this->column]);
        $this->assertNotEmpty($response['columns'][$this->column]);
    }

    private function assertUsersRemovedFromProcessedReport($response)
    {
        $table = $response['reportData'];
        $this->assertEquals(array(false), $table->getColumn($this->column));
        $this->assertEquals(array(3), $table->getColumn('nb_visits'));
        $this->assertArrayNotHasKey($this->column, $response['metadata']['metrics']);
        $this->assertArrayNotHasKey($this->column, $response['metadata']['metricsDocumentation']);
        $this->assertArrayNotHasKey($this->column, $response['columns']);
    }

    private function requestProcessedGetReport()
    {
        return Request::processRequest('API.getProcessedReport', array(
            'idSite' => 1,
            'period' => 'day',
            'date'   => $this->date,
            'apiModule' => 'VisitsSummary',
            'apiAction' => 'get'
        ));
    }

    private function trackPageviewsWithUsers()
    {
        $this->trackPageviewsWithDifferentUsers(array('user1', false, 'user3'));
    }

    private function trackPageviewsWithoutUsers()
    {
        $this->trackPageviewsWithDifferentUsers(array(false, false, false));
    }

    private function trackPageviewsWithDifferentUsers($userIds)
    {
        $tracker = $this->getTracker();

        foreach ($userIds as $index => $userId) {
            $tracker->setForceNewVisit();
            $this->trackPageview($tracker, $userId, '/index/' . $index . '.html');
        }
    }

    private function trackPageview(\PiwikTracker $tracker, $userId, $url = null)
    {
        if (null !== $url) {
            $tracker->setUrl('http://www.example.org' . $url);
        }

        $tracker->setUserId($userId);

        $title = $url ? : 'test';

        $tracker->doTrackPageView($title);
    }

    private function getTracker()
    {
        $tracker = Fixture::getTracker(1, $this->date . ' 00:01:01', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        return $tracker;
    }

    private function setSuperUser()
    {
        $pseudoMockAccess = new FakeAccess();
        $pseudoMockAccess::setSuperUserAccess(true);
        Access::setSingletonInstance($pseudoMockAccess);
    }
}
