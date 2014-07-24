<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Date;
use Piwik\Tests\IntegrationTestCase;
use Piwik\Tests\Fixtures\VisitsInDifferentTimezones;

/**
 * Test reports using visits for a site with a non-UTC timezone.
 *
 * @group TimezonesTest
 * @group Integration
 */
class TimezonesTest extends IntegrationTestCase
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
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }
}

TimezonesTest::$fixture = new VisitsInDifferentTimezones();