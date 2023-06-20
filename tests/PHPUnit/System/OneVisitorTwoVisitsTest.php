<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\API\Proxy;
use Piwik\Archive;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Exception;

/**
 * This use case covers many simple tracking features.
 * - Tracking Goal by manual trigger, and URL matching, with custom revenue
 * - Tracking the same Goal twice only records it once
 * - Tracks 4 page views: 3 clicks and a file download
 * - URLs parameters exclude is tested
 * - In a returning visit, tracks a Goal conversion
 *   URL matching, with custom referrer and keyword
 *   NO cookie support
 *
 * @group Core
 * @group OneVisitorTwoVisitsTest
 */
class OneVisitorTwoVisitsTest extends SystemTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture = null; // initialized below class

    public function setUp(): void
    {
        Proxy::getInstance()->setHideIgnoredFunctions(false);
    }

    public function tearDown(): void
    {
        Proxy::getInstance()->setHideIgnoredFunctions(true);
    }

    public static function getOutputPrefix()
    {
        return "OneVisitorTwoVisits";
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $idSiteBis = self::$fixture->idSiteEmptyBis;
        $idSiteTer = self::$fixture->idSiteEmptyTer;

        $dateTime = self::$fixture->dateTime;

        $enExtraParam = array('expanded' => 1,
                              'flat' => 1,
                              'include_aggregate_rows' => 0,
                              'translateColumnNames' => 1,
        );
        $bulkUrls = array(
            // Testing with several days
            "idSite=" . $idSite . "&date=2010-03-06,2010-03-07&expanded=1&period=day&method=VisitsSummary.get",
            "idSite=" . $idSite . ",$idSiteBis,$idSiteTer&date=2010-03-06,2010-03-07&expanded=1&period=day&method=VisitsSummary.get",
            "idSite=" . $idSite . "&date=2010-03-06&expanded=1&period=day&method=VisitorInterest.getNumberOfVisitsPerVisitDuration",
            "idSite=" . $idSite . "&date=2010-03-06&expanded=1&period=day&method=UsersManager.getUserPreference&preferenceName=defaultReportDate&userLogin=" . Fixture::ADMIN_USER_LOGIN
        );
        foreach ($bulkUrls as &$url) {
            $url = urlencode($url);
        }

        return array(
            array('all', array('idSite' => $idSite,
                               'date' => $dateTime,
                               'otherRequestParameters' => array(
                                   'hideColumns' => OneVisitorTwoVisits::getValueForHideColumns(),
                               )
            )),

            array('all', array('idSite' => $idSite,
                'date' => $dateTime,
                'format' => 'original',
                'otherRequestParameters' => array(
                    'serialize' => '1',
                ),
                'onlyCheckUnserialize' => true,
            )),
            array('Live.getMostRecentVisitorId', array('idSite' => $idSite,
                'date' => $dateTime,
                'format' => 'original',
                'otherRequestParameters' => array(
                    'serialize' => '1',
                ),
                'onlyCheckUnserialize' => true,
            )),

            // test API.get (for bug that incorrectly reorders columns of CSV output)
            //   note: bug only affects rows after first
            array('API.get', array('idSite'                 => $idSite,
                                   'date'                   => '2009-10-01',
                                   'format'                 => 'csv',
                                   'periods'                => array('month'),
                                   'setDateLastN'           => true,
                                   'otherRequestParameters' => $enExtraParam,
                                   'language'               => 'en',
                                   'testSuffix'             => '_csv')),

            array('API.getBulkRequest', array('format' => 'xml',
                                               'testSuffix' => '_bulk_xml',
                                               'otherRequestParameters' => array('urls' => $bulkUrls))),

            array('API.getBulkRequest', array('format' => 'json',
                                              'testSuffix' => '_bulk_json',
                                              'otherRequestParameters' => array('urls' => $bulkUrls))),

            // test API.getProcessedReport w/ report that is its own 'actionToLoadSubTables'
            array('API.getProcessedReport', array('idSite'        => $idSite,
                                                  'date'          => $dateTime,
                                                  'periods'       => array('week'),
                                                  'apiModule'     => 'Actions',
                                                  'apiAction'     => 'getPageUrls',
                                                  'supertableApi' => 'Actions.getPageUrls',
                                                  'testSuffix'    => '__subtable')),

            // test hideColumns && showColumns parameters
            array('VisitsSummary.get', array('idSite'                 => $idSite, 'date' => $dateTime, 'periods' => 'day',
                                             'testSuffix'             => '_hideColumns_',
                                             'otherRequestParameters' => array(
                                                 'hideColumns' => 'nb_visits_converted,max_actions,bounce_count,nb_hits,'
                                                     . 'nb_visits,nb_actions,sum_visit_length,avg_time_on_site'
                                             ))),
            array('VisitsSummary.get', array('idSite'                 => $idSite, 'date' => $dateTime, 'periods' => 'day',
                                             'testSuffix'             => '_showColumns_',
                                             'otherRequestParameters' => array(
                                                 'showColumns' => 'nb_visits,nb_actions,nb_hits'
                                             ))),
            array('VisitsSummary.get', array('idSite'                 => $idSite, 'date' => $dateTime, 'periods' => 'day',
                                             'testSuffix'             => '_hideAllColumns_',
                                             'otherRequestParameters' => array(
                                                 'hideColumns' => 'nb_visits_converted,max_actions,bounce_count,nb_hits,'
                                                     . 'nb_visits,nb_actions,sum_visit_length,avg_time_on_site,'
                                                     . 'bounce_rate,nb_uniq_visitors,nb_actions_per_visit,'
                                             ))),

            // test hideColumns w/ API.getProcessedReport
            array('API.getProcessedReport', array('idSite'                 => $idSite, 'date' => $dateTime,
                                                  'periods'                => 'day', 'apiModule' => 'Actions',
                                                  'apiAction'              => 'getPageTitles', 'testSuffix' => '_hideColumns_',
                                                  'otherRequestParameters' => array(
                                                      'hideColumns' => 'nb_visits_converted,xyzaug,entry_nb_visits,' .
                                                          'bounce_rate,nb_hits,nb_visits,avg_time_on_page,' .
                                                          'avg_time_generation,nb_hits_with_time_generation'
                                                  ))),

            array('API.getProcessedReport', array('idSite'                 => $idSite, 'date' => $dateTime,
                                                  'periods'                => 'day', 'apiModule' => 'Actions',
                                                  'apiAction'              => 'getPageTitles', 'testSuffix' => '_showColumns_',
                                                  'otherRequestParameters' => array(
                                                      'showColumns' => 'nb_visits_converted,xuena,entry_nb_visits,' .
                                                          'bounce_rate,nb_hits'
                                                  ))),
            array('API.getProcessedReport', array('idSite'                 => $idSite, 'date' => $dateTime,
                                                  'periods'                => 'day', 'apiModule' => 'VisitTime',
                                                  'apiAction'              => 'getVisitInformationPerServerTime',
                                                  'testSuffix'             => '_showColumnsWithProcessedMetrics_',
                                                  'otherRequestParameters' => array(
                                                      'showColumns' => 'nb_visits,revenue'
                                                  ))),

            // showColumns with only one column and report having no dimension
            array('API.getProcessedReport', array('idSite'                 => $idSite, 'date' => $dateTime,
                                                  'periods'                => 'day', 'apiModule' => 'VisitsSummary',
                                                  'apiAction'              => 'get',
                                                  'testSuffix'             => '_showColumns_onlyOne',
                                                  'otherRequestParameters' => array(
                                                      'showColumns'        => 'nb_visits'
                                                  ))),

            // test hideColumns w/ expanded=1
            array('Actions.getPageTitles', array('idSite'                 => $idSite, 'date' => $dateTime,
                                                 'periods'                => 'day', 'testSuffix' => '_hideColumns_',
                                                 'otherRequestParameters' => array(
                                                     'hideColumns' => 'nb_visits_converted,entry_nb_visits,' .
                                                         'bounce_rate,nb_hits,nb_visits,sum_time_spent,' .
                                                         'entry_sum_visit_length,entry_bounce_count,exit_nb_visits,' .
                                                         'entry_nb_uniq_visitors,exit_nb_uniq_visitors,entry_nb_actions,' .
                                                         'avg_time_generation,nb_hits_with_time_generation',
                                                     'expanded'    => '1'
                                                 ))),

            // test showColumns on API.get
            array('API.get', array(
                'idSite'                 => $idSite,
                'date'                   => $dateTime,
                'periods'                => 'day',
                'testSuffix'             => '_showColumns',
                'otherRequestParameters' => array(
                    'showColumns'        => 'nb_uniq_visitors,nb_pageviews,bounce_rate'
                )
            )),
        );
    }

    /**
     * Test that restricting the number of sites to those viewable to another login
     * works when building an archive query object.
     */
    public function testArchiveSitesWhenRestrictingToLogin()
    {
        try
        {
            Archive::build(
                'all', 'day', self::$fixture->dateTime, $segment = false, $_restrictToLogin = 'anotherLogin');
            $this->fail("Restricting sites to invalid login did not return 0 sites.");
        }
        catch (Exception $ex)
        {
            // pass
        }
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Config' => \Piwik\DI::decorate(function ($previous) {
                $general = $previous->General;
                $general['action_title_category_delimiter'] = "/";
                $previous->General = $general;
                return $previous;
            }),
        );
    }
}

OneVisitorTwoVisitsTest::$fixture = new OneVisitorTwoVisits();
