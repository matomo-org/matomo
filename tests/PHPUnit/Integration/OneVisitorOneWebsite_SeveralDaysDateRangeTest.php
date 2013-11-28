<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Use case testing various important features:
 * - Test Multisites API use cases
 * - testing period=range use case.
 * - Recording data before and after, checking that the requested range is processed correctly
 * - and more
 */
class Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
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

            //---------------------------------------
            // THEN some Socials tests. Share these...
            array('Referrers.getSocials', array('idSite'  => 'all',
                                               'date'    => '2010-12-13,2011-01-18',
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

            array('Referrers.getUrlsForSocial', array('idSite'        => 1, // test w/ idSubtable
                                                     'date'          => '2010-12-13,2011-01-18',
                                                     'periods'       => 'range',
                                                     'supertableApi' => 'Referrers.getSocials')),
        );
    }

    public static function getOutputPrefix()
    {
        return 'oneVisitor_oneWebsite_severalDays_DateRange';
    }
}

Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange::$fixture
    = new Test_Piwik_Fixture_VisitsOverSeveralDays();

