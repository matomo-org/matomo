<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManyVisitsWithGeoIP;

/**
 * Tests w/ 14 visitors w/ 2 visits each.
 * Uses geoip location provider to test city/region reports.
 *
 * TODO Test ServerBased GeoIP implementation somehow. (Use X-FORWARDED-FOR?)
 * TODO Test PECL implementation somehow. (The PECL module must point to the test dir, not the real one.)
 *
 * @group ManyVisitorsOneWebsiteTest
 * @group Core
 */
class ManyVisitorsOneWebsiteTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

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
        $dateTime = self::$fixture->dateTime;

        $dateString = Date::factory($dateTime)->toString();

        // Note: we must set  'UserCountry.getLocationFromIP' since it's "excluded" by default in setApiNotToCall
        $apiToCall = array('UserCountry');

        $apiToTest = array(
            array($apiToCall,
                  array('idSite'  => $idSite,
                        'date'    => $dateTime,
                        'periods' => array('month'))),

            array($apiToCall, array('idSite'     => $idSite,
                                    'date'       => $dateTime,
                                    'periods'    => array('month'),
                                    'testSuffix' => '_segment_region',
                                    'segment'    => 'regionCode==WAR;countryCode==gb')),

            array($apiToCall, array('idSite'     => $idSite,
                                    'date'       => $dateTime,
                                    'periods'    => array('month'),
                                    'testSuffix' => '_segment_city',
                                    'segment'    => 'city==Stratford-upon-Avon;regionCode==WAR;countryCode==gb')),

            array($apiToCall, array('idSite'     => $idSite,
                                    'date'       => $dateTime,
                                    'periods'    => array('month'),
                                    'testSuffix' => '_segment_lat_long',
                                    'segment'    => 'latitude>45;latitude<49.3;longitude>-125;longitude<-122')),

            array('UserCountry.getCountry', array('idSite'     => $idSite,
                                                  'date'       => $dateTime,
                                                  'periods'    => array('month'),
                                                  'testSuffix' => '_segment_continent',
                                                  'segment'    => 'continentCode==eur')),

            // make sure it is possible to sort getProcessedReport by a processed metric
            array('API.getProcessedReport', array('idSite'                 => $idSite,
                                                  'date'                   => $dateTime,
                                                  'periods'                => 'day',
                                                  'apiModule'              => 'Actions',
                                                  'apiAction'              => 'getPageUrls',
                                                  'testSuffix'             => '_sortByProcessedMetric',
                                                  'otherRequestParameters' => array(
                                                      'filter_sort_column' => 'nb_actions_per_visit'
                                                  ))),

            // make sure it is possible to sort getProcessedReport by a processed metric
            // it should not remove empty rows if report has constant rows count
            array('API.getProcessedReport', array('idSite'                 => $idSite,
                                                  'date'                   => $dateTime,
                                                  'periods'                => 'day',
                                                  'apiModule'              => 'VisitTime',
                                                  'apiAction'              => 'getVisitInformationPerServerTime',
                                                  'testSuffix'             => '_sortByProcessedMetric_constantRowsCountShouldKeepEmptyRows',
                                                  'otherRequestParameters' => array(
                                                      'filter_sort_column' => 'nb_actions_per_visit'
                                                  ))),

            array(array('UserCountry.getLocationFromIP', 'Live.getLastVisitsDetails'), array(
                'idSite'                 => $idSite,
                'date'                   => $dateTime,
                'periods'                => array('month'),
                'otherRequestParameters' => array('ip' => '194.57.91.215')
            )),

            array(array('UserCountry.getLocationFromIP'), array(
                'otherRequestParameters' => array('ip' => '194.57.91.215')
            )),

            array(array('UserCountry.getLocationFromIP'), array(
                'testSuffix' => '_IPv6',
                'otherRequestParameters' => array(
                    'ip' => '2003:f6:93bf:26f:9ec7:a6ff:fe29:27df',
                )
            )),
        );

        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_Live.getLastVisitsDetails_sortDesc',
            'otherRequestParameters' => array('filter_sort_order' => 'desc', 'filter_limit' => 7)
        ));

        // #5950
        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_Live.getLastVisitsDetails_sortByIdVisit',
            'otherRequestParameters' => array('filter_sort_order' => 'desc', 'filter_sort_column' => 'idVisit', 'filter_limit' => 7)
        ));

        // #7458
        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_Live.getLastVisitsDetails_offsetAndLimit_1',
            'otherRequestParameters' => array('filter_offset' => '1', 'filter_limit' => 3)
        ));
        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_Live.getLastVisitsDetails_offsetAndLimit_2',
            'otherRequestParameters' => array('filter_offset' => '4', 'filter_limit' => 3)
        ));

        // #13785
        // check that not contains / not equals action segments filter away all visits having any matching action
        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_pageurlNotContainsSegment',
            'segment'                => 'pageUrl!@quest'
        ));
        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_siteSearchCategoryNotEqualsSegment',
            'segment'                => 'siteSearchCategory!=CAT'
        ));

        // #8324
        // testing filter_excludelowpop and filter_excludelowpop_value
        $apiToTest[] = array('UserCountry.getCountry', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_getCountry_with_filter_excludelowpop',
            'otherRequestParameters' => array('filter_excludelowpop' => 'nb_visits', 'filter_excludelowpop_value' => 5)
        ));

        $apiToTest[] = array('Live.getLastVisitsDetails', array(
            'idSite'                 => $idSite,
            'date'                   => $dateString,
            'periods'                => 'month',
            'testSuffix'             => '_Live.getLastVisitsDetails_sortAsc',
            'otherRequestParameters' => array('filter_sort_order' => 'asc', 'filter_limit' => 7)
        ));

        return $apiToTest;
    }
}

ManyVisitorsOneWebsiteTest::$fixture = new ManyVisitsWithGeoIP();
