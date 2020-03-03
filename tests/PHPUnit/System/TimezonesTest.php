<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitsInDifferentTimezones;

/**
 * Test reports using visits for a site with a non-UTC timezone.
 *
 * @group TimezonesTest
 * @group Core
 */
class TimezonesTest extends SystemTestCase
{
    /**
     * @var VisitsInDifferentTimezones
     */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();

        self::$fixture->setMockNow();

        $this->markTestSkipped("NOTE: currently timezone handling is broken in Date, so this doesn't work as expected.\n"
            . "see https://github.com/matomo-org/matomo/issues/13829 for more info.");
    }

    public function getApiForTesting()
    {
        // NOTE: currently timezone handling is broken in Date, so this doesn't work as expected.
        //
        return array(
            // should have 1 visit per site
            array('Live.getLastVisitsDetails', array('idSite' => 'all',
                                                     'date'   => Date::yesterday() . ',' . Date::today(),
                                                     'period' => 'range')),

            // should have 1 visit for site in UTC (idSite = 2), 0 for site in EST (idSite = 1)
            array('VisitsSummary.get', array('idSite' => 'all',
                                             'date'   => 'yesterday',
                                             'period' => 'day',
                                             'testSuffix' => '_yesterday')),

            // should have 1 visit for site in EST (idSite = 1), 0 for site in UTC (idSite = 2)
            array('VisitsSummary.get', array('idSite' => 'all',
                                             'date'   => 'today',
                                             'period' => 'day',
                                             'testSuffix' => '_today')),
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