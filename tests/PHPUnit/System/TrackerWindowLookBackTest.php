<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitsOverSeveralDays;

/**
 * Testing that, when using window_look_back_for_visitor with a high value,
 * works well with the use case of a returning visitor being assigned to today's visit
 *
 * @group TrackerWindowLookBackTest
 * @group Plugins
 */
class TrackerWindowLookBackTest extends SystemTestCase
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
            array('VisitsSummary.getVisits', array( 'date'    => '2010-12-01,2011-01-31',
                                                    'periods' => array('range'),
                                                    'idSite' => $idSite,
            ))
        );
    }

    public static function getOutputPrefix()
    {
        return 'TrackerWindowLookBack';
    }
}

TrackerWindowLookBackTest::$fixture = new VisitsOverSeveralDays();
TrackerWindowLookBackTest::$fixture->forceLargeWindowLookBackForVisitor = true;
