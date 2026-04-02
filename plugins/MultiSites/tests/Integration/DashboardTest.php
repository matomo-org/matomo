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
use Piwik\Policy\CnilPolicy;
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
    /** @var Dashboard */
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

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(1, false);
        CnilPolicy::setActiveStatus(2, false);
        CnilPolicy::setActiveStatus(null, false);

        parent::tearDown();
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
            'ai_chatbots_requests'          => 0,
            'previous_ai_chatbots_requests' => 0,
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
                'ai_chatbots_requests'                 => 0,
                'ai_chatbots_requests_evolution'       => '0%',
                'ai_chatbots_requests_evolution_trend' => 0,
                'previous_ai_chatbots_requests'        => 0,
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
                'ai_chatbots_requests'                 => 0,
                'ai_chatbots_requests_evolution'       => '0%',
                'ai_chatbots_requests_evolution_trend' => 0,
                'previous_ai_chatbots_requests'        => 0,
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
                'ai_chatbots_requests'                 => 0,
                'ai_chatbots_requests_evolution'       => '0%',
                'ai_chatbots_requests_evolution_trend' => 0,
                'previous_ai_chatbots_requests'        => 0,
            ],
        ];
        $this->assertEquals($expectedSites, $dashboard->getSites([], $limit = 10));
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
                'ai_chatbots_requests'                 => 0,
                'ai_chatbots_requests_evolution'       => '0%',
                'ai_chatbots_requests_evolution_trend' => 0,
                'previous_ai_chatbots_requests'        => 0,
            ],
        ];
        $dashboard->search('site 2');
        $this->assertEquals($expectedSites, $dashboard->getSites([], $limit = 10));
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

        $expectedSites = $this->buildSitesArray([1, 2, 3, 4, 5, 6, 7, 8]);

        $this->assertEquals($expectedSites, $this->dashboard->getSites([], $limit = 20));
    }

    public function testGetSitesShouldApplyALimit()
    {
        $this->setSitesTable(8);

        $expectedSites = $this->buildSitesArray([1, 2, 3, 4]);

        $this->assertEquals($expectedSites, $this->dashboard->getSites([], $limit = 4));
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

        $expectedSites = [
            [
                'label' => 'group1',
                'nb_visits' => 20,
                'isGroup' => 1,
            ], [
                'label' => 'Site1',
                'nb_visits' => 10,
                'group' => 'group1',
            ], [
                'label' => 'Site3',
                'nb_visits' => 10,
                'group' => 'group1',
            ], [
                'label' => 'group2',
                'nb_visits' => 10,
                'isGroup' => 1,
            ], [
                'label' => 'Site2',
                'nb_visits' => 10,
                'group' => 'group2',
            ], [
                'label' => 'Site8',
                'nb_visits' => 10,
            ],
        ];

        // there will be 4 first level entries (group1, group2, group4 and site8), offset is 5, limit is 6.
        // See https://github.com/piwik/piwik/issues/7854 before there was no site returned since 5 > 4 first level entries

        $this->assertEquals($expectedSites, $this->dashboard->getSites(['filter_offset' => 5], $limit = 6));
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

        $expectedSites = [
            [
                'label' => 'group4', // this group should be the added group, that's why there are 5 entries
                'nb_visits' => 40,
                'isGroup' => 1,
            ], [
                'label' => 'Site6',
                'nb_visits' => 10,
                'group' => 'group4',
            ], [
                'label' => 'Site7',
                'nb_visits' => 10,
                'group' => 'group4',
            ], [
                'label' => 'group1',
                'nb_visits' => 20,
                'isGroup' => 1,
            ], [
                'label' => 'Site1',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
        ];

        $this->assertEquals($expectedSites, $this->dashboard->getSites(['filter_offset' => 3], $limit = 4));
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

        $expectedSites = [
            [
                'label' => 'group1', // do not count group into the limit
                'nb_visits' => 50, // there are 5 matching sites having that group, we only return 3, still result is correct!
                'isGroup' => 1,
            ], [
                'label' => 'Site1',
                'nb_visits' => 10,
                'group' => 'group1',
            ], [
                'label' => 'Site3',
                'nb_visits' => 10,
                'group' => 'group1',
            ], [
                'label' => 'Site15',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
        ];

        $this->assertEquals($expectedSites, $this->dashboard->getSites([], $limit = 4));
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

        $expectedSites = $this->buildSitesArray([1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 100]);

        $this->assertEquals($expectedSites, $this->dashboard->getSites([], $limit = 20));
    }

    public function testSearchNoSiteMatches()
    {
        $this->setSitesTable(100);

        $this->dashboard->search('anYString');

        $this->assertSame(0, $this->dashboard->getNumSites());
        $this->assertEquals([], $this->dashboard->getSites([], $limit = 20));
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
        $expectedSites = [
            [
                'label' => 'group1',
                'nb_visits' => 50,
                'isGroup' => 1,
            ],
            [
                'label' => 'Site1',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
            [
                'label' => 'Site3',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
            [
                'label' => 'Site15',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
            [
                'label' => 'Site16',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
            [
                'label' => 'Site18',
                'nb_visits' => 10,
                'group' => 'group1',
            ],
            [
                'label' => 'group4',
                'nb_visits' => 20,
                'isGroup' => 1,
            ],
            [
                'label' => 'Site4',
                'nb_visits' => 10,
                'group' => 'group4',
            ],
            [
                'label' => 'Site6',
                'nb_visits' => 10,
                'group' => 'group4',
            ],
            [
                'label' => 'group2',
                'nb_visits' => 10,
                'isGroup' => 1,
            ],
            [
                'label' => 'Site2',
                'nb_visits' => 10,
                'group' => 'group2',
            ],
        ];

        // 3 groups + 8 sites having a group.
        $this->assertSame(3 + 8, $this->dashboard->getNumSites());

        $matchingSites = $this->dashboard->getSites([], $limit = 20);
        $this->assertEquals($expectedSites, $matchingSites);

        // test with limit should only return the first results
        $matchingSites = $this->dashboard->getSites([], $limit = 8);
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

        $expectedSites = [
            [
                'label' => 'group4',
                'nb_visits' => 20, // another site belongs to that group which doesn't match that name yet still we need to sum the correct result.
                'isGroup' => 1,
            ],
            [
                'label' => 'Site20',
                'nb_visits' => 10,
                'group' => 'group4',
            ],
            [
                'label' => 'group2',
                'nb_visits' => 10,
                'isGroup' => 1,
            ],
            [
                'label' => 'Site2',
                'nb_visits' => 10,
                'group' => 'group2',
            ],
        ];

        // 2 matching sites + their group
        $this->assertSame(2 + 2, $this->dashboard->getNumSites());

        $matchingSites = $this->dashboard->getSites([], $limit = 20);
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

    public function testGetReturnedSiteIdsCollectsIdsRecursivelyFromGroupedSites()
    {
        $sites = $this->setSitesTable(4);
        foreach ([1, 2, 3, 4] as $siteId) {
            $sites->getRowFromLabel('Site' . $siteId)->setMetadata('idsite', $siteId);
        }

        $this->setGroupForSiteId($sites, 1, 'group1');
        $this->setGroupForSiteId($sites, 3, 'group1');
        $this->dashboard->setSitesTable($sites);

        $actual = $this->invokeDashboardMethod($this->dashboard, 'getReturnedSiteIds');

        $this->assertSame([1, 3, 2, 4], $actual);
    }

    public function testRoundReturnedSitesRoundsHitsOnlyForEnabledSiteRowsAndGroups(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        CnilPolicy::setActiveStatus(1, true);
        CnilPolicy::setActiveStatus(2, false);

        $sites = [
            [
                'label' => 'Site1',
                'idsite' => 1,
                'group' => 'group1',
                'nb_visits' => 13,
                'hits' => 18,
                'previous_hits' => 14,
            ],
            [
                'label' => 'Site2',
                'idsite' => 2,
                'group' => 'group1',
                'nb_visits' => 13,
                'hits' => 18,
                'previous_hits' => 14,
            ],
            [
                'label' => 'group1',
                'isGroup' => 1,
                'nb_visits' => 26,
                'hits' => 36,
                'previous_hits' => 28,
            ],
        ];

        $actual = $this->invokeDashboardMethod($this->dashboard, 'roundReturnedSites', [$sites]);

        $this->assertSame(10, $actual[0]['nb_visits']);
        $this->assertSame(20, $actual[0]['hits']);
        $this->assertSame(10, $actual[0]['previous_hits']);

        $this->assertSame(13, $actual[1]['nb_visits']);
        $this->assertSame(18, $actual[1]['hits']);
        $this->assertSame(14, $actual[1]['previous_hits']);

        $this->assertSame(30, $actual[2]['nb_visits']);
        $this->assertSame(40, $actual[2]['hits']);
        $this->assertSame(30, $actual[2]['previous_hits']);
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
        $sites = [];

        foreach ($siteIds as $siteId) {
            $sites[] = ['label' => 'Site' . $siteId, 'nb_visits' => 10];
        }

        return $sites;
    }

    /**
     * @param mixed[] $arguments
     * @return mixed
     */
    private function invokeDashboardMethod(Dashboard $dashboard, string $methodName, array $arguments = [])
    {
        $reflectionMethod = new \ReflectionMethod(Dashboard::class, $methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($dashboard, $arguments);
    }
}
