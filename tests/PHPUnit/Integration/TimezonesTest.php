<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

/**
 * Test reports using visits for a site with a non-UTC timezone.
 */
class Test_Piwik_Integration_TimezonesTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $date = self::$fixture->date;

        return array(
            // should have 1 visit
            array('Live.getLastVisitsDetails', array('idSite' => $idSite,
                                                     'date'   => $date,
                                                     'period' => 'day')),

            // should have 1 visit
            array('VisitsSummary.get', array('idSite' => $idSite,
                                             'date'   => $date,
                                             'period' => 'day',
                                             'testSuffix' => '_withVisit')),

            // should have no visits
            array('VisitsSummary.get', array('idSite' => $idSite,
                                             'date'   => Date::factory($date)->addDay(1)->getDatetime(),
                                             'period' => 'day',
                                             'testSuffix' => '_dayAfterVisit')),
        );
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

Test_Piwik_Integration_TimezonesTest::$fixture = new Test_Piwik_Fixture_VisitsInDifferentTimezones();