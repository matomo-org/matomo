<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitsOverSeveralDays;

/**
 * Use case testing various important features:
 * - Test Multisites API use cases
 * - testing period=range use case.
 * - Recording data before and after, checking that the requested range is processed correctly
 * - and more
 *
 * @group OneVisitorOneWebsiteSeveralDaysDateRangeTest
 * @group Core
 */
class OneVisitorOneWebsiteSeveralDaysDateRangeTest extends SystemTestCase
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

        return array(
            // FIRST some MultiSites API goodness!

            // range test
            array('MultiSites.getAll', array('date'    => '2010-12-15,2011-01-15',
                                             'periods' => array('range')
                // Testing without &pattern= so should return all sites
            )),

            // range test
            array('MultiSites.getAll', array('date'            => '2010-12-15,2011-01-15',
                                             'periods'         => array('range'),
                                             'testSuffix'      => '_Truncated',
                // Testing with filter_truncate should return an `Others` row
                                             'otherRequestParameters' => ['filter_truncate' => '0'],
            )),

            // test several dates (tests use of IndexedByDate w/ 'date1,date2,etc.')
            array('MultiSites.getAll', array('date'                   => '2010-12-15',
                                             'periods'                => array('day'),
                                             'testSuffix'             => '_IndexedByDate',
                // Testing the pattern to getAll restrict websites using name matching
                                             'otherRequestParameters' => array('pattern' => 'aAa')
            )),

            // test getOne call used in MobileMessaging SMS reports
            array('MultiSites.getOne', array('date'    => '2010-12-15,2011-01-15',
                                             'periods' => array('range'),
                                             'idSite'  => $idSite,
                // Testing without &pattern= so should return all sites
            )),

            // test that multiple periods are not supported
            array('MultiSites.getAll', array('date'       => '2010-12-15,2011-01-15',
                                             'periods'    => array('day'),
                                             'testSuffix' => '_MultipleDatesNotSupported',
            )),

            // test that multiple periods are not supported
            array('MultiSites.getAll', array('date'       => '2010-12-15',
                                             'periods'    => array('day'),
                                             'testSuffix' => '_showColumns',
                                             'otherRequestParameters' => array('showColumns' => 'nb_visits,visits_evolution')
            )),

            //---------------------------------------
            // THEN some Socials tests. Share these...
            array('Referrers.getSocials', array('idSite'  => 'all',
                                               'date'    => '2010-12-13,2011-01-18',
                                               'periods' => array('range'))),
            array('Referrers.getSocials', array('idSite'  => $idSite,
                                               'date'     => '2010-12-13,2011-01-18',
                                                'testSuffix' => '_Flattened',
                                                'otherRequestParameters' => array('flat' => '1'),
                                                'periods' => array('range'))),

            array('Referrers.getSocials', array('idSite'       => 'all',
                                               'date'         => '2010-12-10',
                                               'periods'      => array('day'),
                                               'setDateLastN' => true,
                                               'testSuffix'   => '_IndexedByDate')),

            array('Referrers.getUrlsForSocial', array('idSite'     => 'all', // test w/o idSubtable
                                                     'date'       => '2010-12-13,2011-01-18',
                                                     'periods'    => 'range',
                                                     'testSuffix' => '_noIdSubtable')),

            array('Referrers.getUrlsForSocial', array('idSite'       => 1, // test w/ idSubtable
                                                     'date'          => '2010-12-13,2011-01-18',
                                                     'periods'       => 'range',
                                                     'supertableApi' => 'Referrers.getSocials')),

            array('UserCountry.getCountry', array('idSite'  => $idSite,
                                                  'date'    => '2010-12-01,2011-01-15',
                                                  'periods' => array('range'))),
        );
    }

    public static function getOutputPrefix()
    {
        return 'oneVisitor_oneWebsite_severalDays_DateRange';
    }
}

OneVisitorOneWebsiteSeveralDaysDateRangeTest::$fixture = new VisitsOverSeveralDays();
