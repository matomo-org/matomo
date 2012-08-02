<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

require_once dirname(__FILE__) . '/TwoVisitors_TwoWebsites_DifferentDaysTest.php';

/**
 * TODO
 */
class Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays_ArchivingDisabled extends Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays
{
    public static function setUpBeforeClass()
    {
        IntegrationTestCase::setUpBeforeClass();
        self::$allowConversions = true;
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        TwoVisitors_TwoWebsites_DifferentDays_ArchivingDisabled
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $periods = array('day', 'week', 'month', 'year');

        return array(
            // disable archiving & check that there is no archive data
            array('VisitsSummary.get', array('idSite'           => 'all',
                                             'date'             => self::$dateTime,
                                             'periods'          => $periods,
                                             'disableArchiving' => true,
                                             'testSuffix'       => '_disabledBefore')),

            // re-enable archiving & check the output
            array('VisitsSummary.get', array('idSite'           => 'all',
                                             'date'             => self::$dateTime,
                                             'periods'          => $periods,
                                             'disableArchiving' => false,
                                             'testSuffix'       => '_enabled')),

            // diable archiving again & check the output
            array('VisitsSummary.get', array('idSite'           => 'all',
                                             'date'             => self::$dateTime,
                                             'periods'          => $periods,
                                             'disableArchiving' => true,
                                             'testSuffix'       => '_disabledAfter')),
        );
    }

    public function getOutputPrefix()
    {
        return 'TwoVisitors_twoWebsites_differentDays_ArchivingDisabled';
    }
}
