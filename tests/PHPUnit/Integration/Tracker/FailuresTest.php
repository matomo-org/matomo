<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Failures;
use Piwik\Tracker\Request;

/**
 * @group Failures
 * @group FailuresTest
 */
class FailuresTest extends IntegrationTestCase
{
    /**
     * @var Failures
     */
    private $failures;
    private $idSite;
    /**
     * @var Date
     */
    private $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2018-01-02 03:04:05');
        Fixture::createWebsite('2018-01-02 03:04:05');
        $this->now = Date::factory('2018-09-07 01:02:03');
        $this->failures = new Failures();
        $this->failures->setNow($this->now);
    }

    public function test_logFailure_getAllFailures()
    {
        $this->logFailure(1, array());
        $this->logFailure(1, array('idsite' => 9999999)); // unknown idsite
        $this->logFailure(9999, array()); // unknown failure
        $this->logFailure(2, array('url' => ''));
        $this->logFailure(1, array('url' => 'https://www.example.com/page'));
        $failures = $this->failures->getAllFailures();
        $this->assertEquals(array (
                array (
                    'idsite' => '1',
                    'idfailure' => '1',
                    'date_first_occurred' => '2018-09-07 01:02:03',
                    'request_url' => 'rec=1&idsite=1',
                    'site_name' => 'Piwik test',
                    'pretty_date_first_occurred' => 'Intl_1or02Intl_Time_AMt_250Intl_Time_AMtTi02_S1ort',
                    'url' => '',
                    'solution_url' => 'https://matomo.org/faq/how-to/faq_30838/',
                    'problem' => 'CoreAdminHome_TrackingFailureInvalidSiteProblem',
                    'solution' => 'CoreAdminHome_TrackingFailureInvalidSiteSolution',
                ),
                array (
                    'idsite' => '1',
                    'idfailure' => '2',
                    'date_first_occurred' => '2018-09-07 01:02:03',
                    'request_url' => 'url=&rec=1&idsite=1',
                    'site_name' => 'Piwik test',
                    'pretty_date_first_occurred' => 'Intl_1or02Intl_Time_AMt_250Intl_Time_AMtTi02_S1ort',
                    'url' => '',
                    'solution_url' => 'https://matomo.org/faq/how-to/faq_30835/',
                    'problem' => 'CoreAdminHome_TrackingFailureAuthenticationProblem',
                    'solution' => 'CoreAdminHome_TrackingFailureAuthenticationSolution',
                ),
                array (
                    'idsite' => '1',
                    'idfailure' => '9999',
                    'date_first_occurred' => '2018-09-07 01:02:03',
                    'request_url' => 'rec=1&idsite=1',
                    'site_name' => 'Piwik test',
                    'pretty_date_first_occurred' => 'Intl_1or02Intl_Time_AMt_250Intl_Time_AMtTi02_S1ort',
                    'url' => '',
                    'problem' => '',
                    'solution' => '',
                    'solution_url' => '',
                ),
                array (
                    'idsite' => '9999999',
                    'idfailure' => '1',
                    'date_first_occurred' => '2018-09-07 01:02:03',
                    'request_url' => 'idsite=9999999&rec=1',
                    'site_name' => 'General_Unknown',
                    'pretty_date_first_occurred' => 'Intl_1or02Intl_Time_AMt_250Intl_Time_AMtTi02_S1ort',
                    'url' => '',
                    'solution_url' => 'https://matomo.org/faq/how-to/faq_30838/',
                    'problem' => 'CoreAdminHome_TrackingFailureInvalidSiteProblem',
                    'solution' => 'CoreAdminHome_TrackingFailureInvalidSiteSolution',
                ),
        ), $failures);
    }

    public function test_logFailure_doesNotLogSameFailureTwice()
    {
        $expected = array (
            array (
                'idsite' => '1',
                'idfailure' => '1',
                'date_first_occurred' => '2018-09-07 01:02:03',
                'request_url' => 'rec=1&idsite=1',
                'site_name' => 'Piwik test',
                'pretty_date_first_occurred' => 'Intl_1or02Intl_Time_AMt_250Intl_Time_AMtTi02_S1ort',
                'url' => '',
                'solution_url' => 'https://matomo.org/faq/how-to/faq_30838/',
                'problem' => 'CoreAdminHome_TrackingFailureInvalidSiteProblem',
                'solution' => 'CoreAdminHome_TrackingFailureInvalidSiteSolution',
            )
        );

        $this->logFailure(1, array());
        $failures = $this->failures->getAllFailures();
        $this->assertEquals($expected, $failures);

        $this->logFailure(1, array());
        $failures = $this->failures->getAllFailures();
        $this->assertEquals($expected, $failures);

        // does log a different problem for same site
        $this->logFailure(2, array());
        $failures = $this->failures->getAllFailures();
        $this->assertCount(2, $failures);

        // does log a same problem for different site
        $this->logFailure(1, array('idsite' => 999));
        $failures = $this->failures->getAllFailures();
        $this->assertCount(3, $failures);
    }

    public function test_logFailure_anonymizesTokenWhenParamUsed()
    {
        $this->logFailure(1, array('token_auth' => 'foobar', 'token' => 'bar', 'tokenauth' => 'baz'));
        $failures = $this->failures->getAllFailures();
        $this->assertEquals('token_auth=__TOKEN_AUTH__&token=__TOKEN_AUTH__&tokenauth=__TOKEN_AUTH__&rec=1&idsite=1', $failures[0]['request_url']);
    }

    public function test_logFailure_anonymizesTokenWhenMd5ValueUsed()
    {
        $this->logFailure(1, array('foo' => md5('foo')));
        $failures = $this->failures->getAllFailures();
        $this->assertEquals('foo=__TOKEN_AUTH__&rec=1&idsite=1', $failures[0]['request_url']);
    }

    public function test_logFailure_anonymizesTokenWhenMd5SimilarValueUsed()
    {
        $this->logFailure(1, array('foo' => md5('foo') . 'ff'));
        $failures = $this->failures->getAllFailures();
        $this->assertEquals('foo=__TOKEN_AUTH__&rec=1&idsite=1', $failures[0]['request_url']);
    }

    public function test_logFailure_doesNotLogExcludedRequest()
    {
        $this->logFailure(1, array('rec' => '0'));
        $this->assertEquals(array(), $this->failures->getAllFailures());
    }

    public function test_logFailure_doesNotLogAnyUnusualHighSiteId()
    {
        $this->logFailure(1, array('idsite' => '99999999999'));
        $this->assertEquals(array(), $this->failures->getAllFailures());
    }

    public function test_logFailure_doesNotLogAnyUnusualLowSiteId()
    {
        try {
            $this->logFailure(1, array('idsite' => '-1'));
        } catch (UnexpectedWebsiteFoundException $e) {
            // triggered by $request->getIdSite() in visits excluded... we ignore this error in this test
            // as it is fine to have this error as long as the failure is not recorded
        }
        $this->assertEquals(array(), $this->failures->getAllFailures());
    }

    public function test_logFailure_canLogEntryForIdSite0()
    {
        $this->logFailure(1, array('idsite' => '0'));
        $this->assertCount(1, $this->failures->getAllFailures());
    }

    public function test_getAllFailures_noFailuresByDefault()
    {
        $this->assertSame(array(), $this->failures->getAllFailures());
    }

    public function test_getFailuresForSites_noFailuresByDefault()
    {
        $this->assertSame(array(), $this->failures->getAllFailures());
    }

    public function test_getFailuresForSites_returnsOnlyFailuresForGivenSite()
    {
        $this->logFailure(1, array('idsite' => 2));
        $this->logFailure(2, array('idsite' => 2));
        $this->logFailure(1, array('idsite' => 3));
        $this->logFailure(2, array('idsite' => 3));
        $this->logFailure(3, array('idsite' => 3));
        $this->logFailure(1, array('idsite' => 4));
        $this->logFailure(2, array('idsite' => 4));
        $this->logFailure(3, array('idsite' => 4));
        $this->logFailure(4, array('idsite' => 4));
        $this->logFailure(1, array('idsite' => 5));
        $this->logFailure(2, array('idsite' => 5));
        $this->logFailure(3, array('idsite' => 5));
        $this->logFailure(4, array('idsite' => 5));
        $this->logFailure(5, array('idsite' => 5));
        $this->assertSame(array(), $this->failures->getFailuresForSites(array()));
        $this->assertCount(2, $this->failures->getFailuresForSites(array(2)));
        $this->assertCount(3, $this->failures->getFailuresForSites(array(3)));
        $this->assertCount(7, $this->failures->getFailuresForSites(array(2,5)));
        $this->assertCount(12, $this->failures->getFailuresForSites(array(4,3,5)));
    }

    public function test_deleteTrackingFailure()
    {
        $this->logFailure(1, array('idsite' => 2));
        $this->logFailure(2, array('idsite' => 2));
        $this->logFailure(1, array('idsite' => 3));
        $this->logFailure(2, array('idsite' => 3));
        $this->logFailure(3, array('idsite' => 3));
        $this->assertCount(5, $this->failures->getAllFailures());

        $this->failures->deleteTrackingFailure(3, 2);

        $summary = $this->getFailureSummary();
        $this->assertEquals(array(
            array(2,1), array(2,2), array(3,1), array(3,3), // 3,2 is not returned
        ), $summary);
    }

    public function test_deleteTrackingFailureWhenWrongIdAllAreKept()
    {
        $this->logFailure(1, array('idsite' => 2));
        $this->logFailure(2, array('idsite' => 2));
        $this->logFailure(1, array('idsite' => 3));
        $this->logFailure(2, array('idsite' => 3));
        $this->logFailure(3, array('idsite' => 3));
        $this->assertCount(5, $this->failures->getAllFailures());

        $this->failures->deleteTrackingFailure(99999, 2);
        $this->assertCount(5, $this->failures->getAllFailures());
        $this->failures->deleteTrackingFailure(2, 9999);
        $this->assertCount(5, $this->failures->getAllFailures());
    }

    public function test_deleteAllTrackingFailures()
    {
        $this->logFailure(1, array('idsite' => 2));
        $this->logFailure(2, array('idsite' => 2));
        $this->logFailure(1, array('idsite' => 3));
        $this->logFailure(2, array('idsite' => 3));
        $this->logFailure(3, array('idsite' => 3));
        $this->assertCount(5, $this->failures->getAllFailures());

        $this->failures->deleteAllTrackingFailures();
        $this->assertSame([], $this->failures->getAllFailures());
    }

    public function test_deleteTrackingFailures()
    {
        $this->logFailure(1, array('idsite' => 1));
        $this->logFailure(1, array('idsite' => 2));
        $this->logFailure(2, array('idsite' => 2));
        $this->logFailure(1, array('idsite' => 3));
        $this->logFailure(2, array('idsite' => 3));
        $this->logFailure(3, array('idsite' => 3));
        $this->assertCount(6, $this->failures->getAllFailures());

        $this->failures->deleteTrackingFailures(array(1,3));
        $this->assertEquals([array(2,1), array(2,2)], $this->getFailureSummary());
    }

    public function test_removeFailuresOlderThanDays()
    {
        $this->logFailure(1, array('idsite' => 2));
        $this->logFailure(2, array('idsite' => 2));
        $this->logFailure(3, array('idsite' => 2), 1);
        $this->logFailure(1, array('idsite' => 3), 2);
        $this->logFailure(2, array('idsite' => 3), 2);
        $this->logFailure(3, array('idsite' => 3), 3);
        $this->logFailure(4, array('idsite' => 3), 3);
        $this->logFailure(5, array('idsite' => 3), 3);
        $this->logFailure(6, array('idsite' => 3), 4);

        $this->failures->removeFailuresOlderThanDays(2);

        $summary = $this->getFailureSummary();
        $this->assertEquals(array(
            array(2,1), array(2,2), array(2,3), array(3,1), array(3,2)
        ), $summary);
    }

    private function getFailureSummary()
    {
        $failures = $this->failures->getAllFailures();

        $summary = array();
        foreach ($failures as $failure) {
            $summary[] = array($failure['idsite'], $failure['idfailure']);
        }
        return $summary;
    }

    private function logFailure($idFailure, $params, $daysAgo = null)
    {
        if (!isset($params['rec'])) {
            $params['rec'] = 1;
        }
        if (!isset($params['idsite'])) {
            $params['idsite'] = $this->idSite;
        }
        $request = new Request($params);
        if (isset($daysAgo)) {
            $this->failures->setNow($this->now->subDay($daysAgo)->addPeriod(1, 'minute'));
        }
        $this->failures->logFailure($idFailure, $request);
        $this->failures->setNow($this->now);
    }
}
