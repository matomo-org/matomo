<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\tests\Integration;

use Piwik\DataTable;
use Piwik\Period;
use Piwik\Plugins\MultiSites\Dashboard;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group MultiSites
 * @group DashboardTest
 * @group Dashboard
 * @group Plugins
 */
class DashboardTest extends IntegrationTestCase
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    private $numSitesToCreate = 3;

    public function setUp(): void
    {
        parent::setUp();

        for ($i = 1; $i <= $this->numSitesToCreate; $i++) {
            Fixture::createWebsite('2012-12-12 00:00:00', $ecommerce = 0, 'Site ' . $i);
        }

        Fixture::loadAllTranslations();

        $this->dashboard = $this->getMockBuilder('Piwik\Plugins\MultiSites\Dashboard')
                                ->setMethods(null)
                                ->disableOriginalConstructor()
                                ->getMock();
    }

    public function testConstructShouldFetchSitesWithNeededColumnsAndReturnEvenSitesHavingNoVisits()
    {
        $dayToFetch = '2012-12-13';
        $lastDate   = '2012-12-12';

        $dashboard = new Dashboard('day', $dayToFetch, false);

        $this->assertSame($this->numSitesToCreate, $dashboard->getNumSites());
        $this->assertEquals($lastDate, $dashboard->getLastDate());

        $expectedTotals = [
            'nb_pageviews' => 0,
            'nb_visits' => 0,
            'nb_actions' => 0,
            'revenue' => 0,
            'hits' => 0,
            'previous_nb_pageviews' => 0,
            'previous_nb_visits' => 0,
            'previous_hits' => 0,
            'previous_nb_actions' => 0,
            'previous_revenue' => 0,
        ];
        $this->assertEquals($expectedTotals, $dashboard->getTotals());

        $expectedSites = [
            [
                'label' => 'Site 1',
                'nb_visits' => 0,
                'nb_actions' => 0,
                'nb_pageviews' => 0,
                'revenue' => 0,
                'visits_evolution' => '0%',
                'actions_evolution' => '0%',
                'pageviews_evolution' => '0%',
                'revenue_evolution' => '0%',
                'idsite' => 1,
                'group' => '',
                'main_url' => 'http://piwik.net',
                'nb_conversions' => 0,
                'nb_conversions_evolution' => '0%',
                'ratio' => 1,
                'previous_nb_visits' => 0,
                'periodName' => 'day',
                'previousRange' => 'Wed, Dec 12',
                'previous_nb_actions' => 0,
                'visits_evolution_trend' => 0,
                'actions_evolution_trend' => 0,
                'pageviews_evolution_trend' => 0,
                'revenue_evolution_trend' => 0,
                'nb_conversions_evolution_trend' => 0,
                'currencySymbol' => '$',
                'hits' => 0,
                'hits_evolution' => '0%',
                'hits_evolution_trend' => 0,
                'previous_nb_pageviews' => 0,
                'previous_hits' => 0,
                'previous_revenue' => 0,
                'previous_nb_conversions' => 0,
                'previous_nb_actions' => 0,
            ],
            [
                'label' => 'Site 2',
                'nb_visits' => 0,
                'nb_actions' => 0,
                'nb_pageviews' => 0,
                'revenue' => 0,
                'visits_evolution' => '0%',
                'actions_evolution' => '0%',
                'pageviews_evolution' => '0%',
                'revenue_evolution' => '0%',
                'idsite' => 2,
                'group' => '',
                'main_url' => 'http://piwik.net',
                'nb_conversions' => 0,
                'nb_conversions_evolution' => '0%',
                'ratio' => 1,
                'previous_nb_visits' => 0,
                'periodName' => 'day',
                'previousRange' => 'Wed, Dec 12',
                'previous_nb_actions' => 0,
                'visits_evolution_trend' => 0,
                'actions_evolution_trend' => 0,
                'pageviews_evolution_trend' => 0,
                'revenue_evolution_trend' => 0,
                'nb_conversions_evolution_trend' => 0,
                'currencySymbol' => '$',
                'hits' => 0,
                'hits_evolution' => '0%',
                'hits_evolution_trend' => 0,
                'previous_nb_pageviews' => 0,
                'previous_hits' => 0,
                'previous_revenue' => 0,
                'previous_nb_conversions' => 0,
                'previous_nb_actions' => 0,
            ],
            [
                'label' => 'Site 3',
                'nb_visits' => 0,
                'nb_actions' => 0,
                'nb_pageviews' => 0,
                'revenue' => 0,
                'visits_evolution' => '0%',
                'actions_evolution' => '0%',
                'pageviews_evolution' => '0%',
                'revenue_evolution' => '0%',
                'idsite' => 3,
                'group' => '',
                'main_url' => 'http://piwik.net',
                'nb_conversions' => 0,
                'nb_conversions_evolution' => '0%',
                'ratio' => 1,
                'previous_nb_visits' => 0,
                'periodName' => 'day',
                'previousRange' => 'Wed, Dec 12',
                'previous_nb_actions' => 0,
                'visits_evolution_trend' => 0,
                'actions_evolution_trend' => 0,
                'pageviews_evolution_trend' => 0,
                'revenue_evolution_trend' => 0,
                'nb_conversions_evolution_trend' => 0,
                'currencySymbol' => '$',
                'hits' => 0,
                'hits_evolution' => '0%',
                'hits_evolution_trend' => 0,
                'previous_nb_pageviews' => 0,
                'previous_hits' => 0,
                'previous_revenue' => 0,
                'previous_nb_conversions' => 0,
                'previous_nb_actions' => 0,
            ],
        ];
        $this->assertEquals($expectedSites, $dashboard->getSites(array(), $limit = 10));
    }

    public function testConstructShouldActuallyFindSitesWhenSeaching()
    {
        $dashboard = new Dashboard('day', '2012-12-13', false);
        $this->assertSame($this->numSitesToCreate, $dashboard->getNumSites());

        $expectedSites = [
            [
                'label' => 'Site 2',
                'nb_visits' => 0,
                'nb_actions' => 0,
                'nb_pageviews' => 0,
                'revenue' => 0,
                'visits_evolution' => '0%',
                'actions_evolution' => '0%',
                'pageviews_evolution' => '0%',
                'revenue_evolution' => '0%',
                'idsite' => 2,
                'group' => '',
                'main_url' => 'http://piwik.net',
                'nb_conversions' => 0,
                'nb_conversions_evolution' => '0%',
                'ratio' => 1,
                'previous_nb_visits' => 0,
                'periodName' => 'day',
                'previousRange' => 'Wed, Dec 12',
                'visits_evolution_trend' => 0,
                'actions_evolution_trend' => 0,
                'pageviews_evolution_trend' => 0,
                'revenue_evolution_trend' => 0,
                'nb_conversions_evolution_trend' => 0,
                'currencySymbol' => '$',
                'hits' => 0,
                'hits_evolution' => '0%',
                'hits_evolution_trend' => 0,
                'previous_nb_pageviews' => 0,
                'previous_hits' => 0,
                'previous_revenue' => 0,
                'previous_nb_conversions' => 0,
                'previous_nb_actions' => 0,
            ],
        ];
        $dashboard->search('site 2');
        $this->assertEquals($expectedSites, $dashboard->getSites(array(), $limit = 10));
        $this->assertSame(1, $dashboard->getNumSites());
    }

    public function testGetNumSitesShouldBeZeroIfNoSitesAreSet()
    {
        $this->assertSame(0, $this->dashboard->getNumSites());
    }

    public function testGetNumSitesShouldReturnTheNumberOfSetSites()
    {
        $this->setSitesTable(4);

        $this->assertSame(4, $this->dashboard->getNumSites());
    }

    public function testGetNumSitesShouldCountGroupsIntoResult()
    {
        $sites = $this->setSitesTable(20);

        $this->setGroupForSiteId($sites, $siteId = 1, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 2, 'group2');
        $this->setGroupForSiteId($sites, $siteId = 3, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 4, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 15, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 16, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 18, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 6, 'group4');
        $this->dashboard->setSitesTable($sites);

        // 3 different groups
        $this->assertSame(20 + 3, $this->dashboard->getNumSites());
    }

    public function testGetSitesShouldReturnAnArrayOfSites()
    {
        $this->setSitesTable(8);

        $expectedSites = $this->buildSitesArray(array(1, 2, 3, 4, 5, 6, 7, 8));

        $this->assertEquals($expectedSites, $this->dashboard->getSites(array(), $limit = 20));
    }

    public function testGetSitesShouldApplyALimit()
    {
        $this->setSitesTable(8);

        $expectedSites = $this->buildSitesArray(array(1, 2, 3, 4));

        $this->assertEquals($expectedSites, $this->dashboard->getSites(array(), $limit = 4));
    }

    public function testGetSitesShouldApplyLimitCorrectIfThereAreLessFirstLevelRowsThenLimit()
    {
        $sites = $this->setSitesTable(8);

        $this->setGroupForSiteId($sites, $siteId = 1, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 2, 'group2');
        $this->setGroupForSiteId($sites, $siteId = 3, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 4, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 5, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 6, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 7, 'group4');
        $this->dashboard->setSitesTable($sites);

        $expectedSites = array(
                array ('label' => 'group1',
                       'nb_visits' => 20,
                       'isGroup' => 1,
             ), array ('label' => 'Site1',
                       'nb_visits' => 10,
                       'group' => 'group1',
             ), array ('label' => 'Site3',
                       'nb_visits' => 10,
                       'group' => 'group1',
             ), array ('label' => 'group2',
                       'nb_visits' => 10,
                       'isGroup' => 1,
             ), array ('label' => 'Site2',
                       'nb_visits' => 10,
                       'group' => 'group2',
             ), array ('label' => 'Site8',
                       'nb_visits' => 10,
             ),
        );

        // there will be 4 first level entries (group1, group2, group4 and site8), offset is 5, limit is 6.
        // See https://github.com/piwik/piwik/issues/7854 before there was no site returned since 5 > 4 first level entries

        $this->assertEquals($expectedSites, $this->dashboard->getSites(array('filter_offset' => 5), $limit = 6));
    }

    public function testGetSitesShouldReturnOneMoreGroupIfFirstSiteBelongsToAGroupButGroupWouldBeNormallyNotInResult()
    {
        $sites = $this->setSitesTable(8);

        $this->setGroupForSiteId($sites, $siteId = 1, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 2, 'group2');
        $this->setGroupForSiteId($sites, $siteId = 3, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 4, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 5, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 6, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 7, 'group4');
        $this->dashboard->setSitesTable($sites);

        $expectedSites = array(
                array ('label' => 'group4', // this group should be the added group, that's why there are 5 entries
                       'nb_visits' => 40,
                       'isGroup' => 1,
             ), array ('label' => 'Site6',
                       'nb_visits' => 10,
                       'group' => 'group4',
             ), array ('label' => 'Site7',
                       'nb_visits' => 10,
                       'group' => 'group4',
             ), array ('label' => 'group1',
                       'nb_visits' => 20,
                       'isGroup' => 1,
             ), array ('label' => 'Site1',
                       'nb_visits' => 10,
                       'group' => 'group1',
             )
        );

        $this->assertEquals($expectedSites, $this->dashboard->getSites(array('filter_offset' => 3), $limit = 4));
    }

    public function testGetSitesWithGroupShouldApplyALimitAndKeepSitesWithinGroup()
    {
        $sites = $this->setSitesTable(20);

        $this->setGroupForSiteId($sites, $siteId = 1, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 2, 'group2');
        $this->setGroupForSiteId($sites, $siteId = 3, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 4, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 15, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 16, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 18, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 6, 'group4');
        $this->dashboard->setSitesTable($sites);

        $expectedSites = array (
            array (
                'label' => 'group1', // do not count group into the limit
                'nb_visits' => 50, // there are 5 matching sites having that group, we only return 3, still result is correct!
                'isGroup' => 1,
            ), array (
                'label' => 'Site1',
                'nb_visits' => 10,
                'group' => 'group1',
            ), array (
                'label' => 'Site3',
                'nb_visits' => 10,
                'group' => 'group1',
            ), array (
                'label' => 'Site15',
                'nb_visits' => 10,
                'group' => 'group1',
            ),
        );

        $this->assertEquals($expectedSites, $this->dashboard->getSites(array(), $limit = 4));
    }

    public function testSearchShouldUpdateTheNumberOfAvailableSites()
    {
        $this->setSitesTable(100);

        $this->dashboard->search('site1');

        // site1 + site1* matches
        $this->assertSame(12, $this->dashboard->getNumSites());
    }

    public function testSearchShouldOnlyKeepMatchingSites()
    {
        $this->setSitesTable(100);

        $this->dashboard->search('site1');

        $expectedSites = $this->buildSitesArray(array(1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 100));

        $this->assertEquals($expectedSites, $this->dashboard->getSites(array(), $limit = 20));
    }

    public function testSearchNoSiteMatches()
    {
        $this->setSitesTable(100);

        $this->dashboard->search('anYString');

        $this->assertSame(0, $this->dashboard->getNumSites());
        $this->assertEquals(array(), $this->dashboard->getSites(array(), $limit = 20));
    }

    public function testSearchWithGroupShouldDoesSearchInGroupNameAndMatchesEvenSitesHavingThatGroupName()
    {
        $sites = $this->setSitesTable(20);

        $this->setGroupForSiteId($sites, $siteId = 1, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 2, 'group2');
        $this->setGroupForSiteId($sites, $siteId = 3, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 4, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 15, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 16, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 18, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 6, 'group4');

        $this->dashboard->setSitesTable($sites);
        $this->dashboard->search('group');

        // groups within that site should be listed first.
        $expectedSites = array (
            array (
                'label' => 'group1',
                'nb_visits' => 50,
                'isGroup' => 1,
            ),
            array (
                'label' => 'Site1',
                'nb_visits' => 10,
                'group' => 'group1',
            ),
            array (
                'label' => 'Site3',
                'nb_visits' => 10,
                'group' => 'group1',
            ),
            array (
                'label' => 'Site15',
                'nb_visits' => 10,
                'group' => 'group1',
            ),
            array (
                'label' => 'Site16',
                'nb_visits' => 10,
                'group' => 'group1',
            ),
            array (
                'label' => 'Site18',
                'nb_visits' => 10,
                'group' => 'group1',
            ),
            array (
                'label' => 'group4',
                'nb_visits' => 20,
                'isGroup' => 1,
            ),
            array (
                'label' => 'Site4',
                'nb_visits' => 10,
                'group' => 'group4',
            ),
            array (
                'label' => 'Site6',
                'nb_visits' => 10,
                'group' => 'group4',
            ),
            array (
                'label' => 'group2',
                'nb_visits' => 10,
                'isGroup' => 1,
            ),
            array (
                'label' => 'Site2',
                'nb_visits' => 10,
                'group' => 'group2',
            ),
        );

        // 3 groups + 8 sites having a group.
        $this->assertSame(3 + 8, $this->dashboard->getNumSites());

        $matchingSites = $this->dashboard->getSites(array(), $limit = 20);
        $this->assertEquals($expectedSites, $matchingSites);

        // test with limit should only return the first results
        $matchingSites = $this->dashboard->getSites(array(), $limit = 8);
        $this->assertEquals(array_slice($expectedSites, 0, 8), $matchingSites);
    }

    public function testSearchWithGroupIfASiteMatchesButNotTheGroupNameItShouldKeepTheGroupThough()
    {
        $sites = $this->setSitesTable(20);

        $this->setGroupForSiteId($sites, $siteId = 1, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 2, 'group2');
        $this->setGroupForSiteId($sites, $siteId = 3, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 20, 'group4');
        $this->setGroupForSiteId($sites, $siteId = 15, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 16, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 18, 'group1');
        $this->setGroupForSiteId($sites, $siteId = 6, 'group4');

        $this->dashboard->setSitesTable($sites);
        $this->dashboard->search('site2');

        $expectedSites = array (
            array (
                'label' => 'group4',
                'nb_visits' => 20, // another site belongs to that group which doesn't match that name yet still we need to sum the correct result.
                'isGroup' => 1,
            ),
            array (
                'label' => 'Site20',
                'nb_visits' => 10,
                'group' => 'group4',
            ),
            array (
                'label' => 'group2',
                'nb_visits' => 10,
                'isGroup' => 1,
            ),
            array (
                'label' => 'Site2',
                'nb_visits' => 10,
                'group' => 'group2',
            ),
        );

        // 2 matching sites + their group
        $this->assertSame(2 + 2, $this->dashboard->getNumSites());

        $matchingSites = $this->dashboard->getSites(array(), $limit = 20);
        $this->assertEquals($expectedSites, $matchingSites);
    }

    public function testGetLastDateShouldReturnTheLastDateIfAnyIsSet()
    {
        $this->setSitesTable(1);

        $this->assertSame('2012-12-12', $this->dashboard->getLastDate());
    }

    public function testGetLastDateShouldReturnAnEmptyStringIfNoLastDateIsSet()
    {
        $this->dashboard->setSitesTable(new DataTable());

        $this->assertSame('', $this->dashboard->getLastDate());
    }

    private function setGroupForSiteId(DataTable $table, $siteId, $groupName)
    {
        $table->getRowFromLabel('Site' . $siteId)->setMetadata('group', $groupName);
    }

    private function setSitesTable($numSites)
    {
        $sites = new DataTable();
        $sites->addRowsFromSimpleArray($this->buildSitesArray(range(1, $numSites)));
        $sites->setMetadata('last_period_date', Period\Factory::build('day', '2012-12-12'));

        $this->dashboard->setSitesTable($sites);

        return $sites;
    }

    private function buildSitesArray($siteIds)
    {
        $sites = array();

        foreach ($siteIds as $siteId) {
            $sites[] = array('label' => 'Site' . $siteId, 'nb_visits' => 10);
        }

        return $sites;
    }
}
