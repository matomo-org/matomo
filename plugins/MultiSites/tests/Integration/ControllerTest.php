<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\tests\Integration;

use Piwik\FrontController;
use Piwik\Plugins\MultiSites\tests\Fixtures\ManySitesWithVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group MultiSites
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends SystemTestCase
{
    /**
     * @var ManySitesWithVisits
     */
    public static $fixture = null; // initialized below class definition

    public function test_getAllWithGroups()
    {
        $sites = $this->requestGetAllWithGroups(array('filter_limit' => 20));
        $this->assertTrue(is_string($sites));

        $sites = json_decode($sites, true);

        // as limit is 20 make sure it returns all 15 sites but we do not check for all the detailed sites info,
        // this is tested in other tests. We only check for first site.
        $this->assertSame(15, count($sites['sites']));
        $this->assertEquals(array(
            'label' => 'Site 1',
            'nb_visits' => '2',
            'nb_actions' => '4',
            'nb_pageviews' => '3',
            'revenue' => '$2,541',
            'visits_evolution' => '100%',
            'actions_evolution' => '100%',
            'pageviews_evolution' => '100%',
            'revenue_evolution' => '100%',
            'idsite' => 1,
            'group' => '',
            'main_url' => 'http://piwik.net',
        ), $sites['sites'][0]);

        unset($sites['sites']);
        $expected = array(
            'numSites' => 15,
            'totals' => array(
                'nb_pageviews' => 8,
                'nb_visits' => 5,
                'nb_actions' => 12,
                'revenue' => '5,082',
                'nb_visits_lastdate' => 0,
            ),
            'lastDate' => '2013-01-22'
        );

        $this->assertEquals($expected, $sites);
    }

    public function test_getAllWithGroups_ifLimitIsApplied_ShouldStill_ReturnCorrectNumberOfSitesAvailable()
    {
        $sites = $this->requestGetAllWithGroups(array('filter_limit' => 5));
        $sites = json_decode($sites, true);

        $this->assertSame(5, count($sites['sites']));
        $this->assertSame(15, $sites['numSites']);
        $this->assertReturnedSitesEquals(array(1, 2, 3, 4, 5), $sites);
    }

    public function test_getAllWithGroups_shouldBeAbleToHandleLimitAndOffset()
    {
        $sites = $this->requestGetAllWithGroups(array('filter_limit' => 5, 'filter_offset' => 4));
        $sites = json_decode($sites, true);

        $this->assertSame(5, count($sites['sites']));
        $this->assertSame(15, $sites['numSites']);
        $this->assertReturnedSitesEquals(array(5, 6, 7, 8, 9), $sites);
    }

    public function test_getAllWithGroups_shouldApplySearchAndReturnInNumSitesOnlyTheNumberOfMatchingSites()
    {
        $pattern = 'Site 1';
        $sites = $this->requestGetAllWithGroups(array('filter_limit' => 5, 'pattern' => $pattern));
        $sites = json_decode($sites, true);

        $this->assertSame(5, count($sites['sites']));
        $this->assertSame(1 + 6, $sites['numSites']); // Site 1 + Site10-15
        $this->assertReturnedSitesEquals(array(1, 10, 11, 12, 13), $sites);
    }

    private function assertReturnedSitesEquals($expectedSiteIds, $sites)
    {
        foreach ($expectedSiteIds as $index => $expectedSiteId) {
            $this->assertSame($expectedSiteId, $sites['sites'][$index]['idsite']);
        }
    }

    private function requestGetAllWithGroups($params)
    {
        $oldGet = $_GET;
        $params['period'] = 'day';
        $params['date']   = '2013-01-23';
        $_GET   = $params;
        $sites  = FrontController::getInstance()->dispatch('MultiSites', 'getAllWithGroups');
        $_GET   = $oldGet;
        return $sites;
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

ControllerTest::$fixture = new ManySitesWithVisits();
