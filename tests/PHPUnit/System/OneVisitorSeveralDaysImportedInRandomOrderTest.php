<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitOverSeveralDaysImportedLogs;

/**
 * @group Core
 * @group OneVisitorSeveralDaysImportedInRandomOrderTest
 */
class OneVisitorSeveralDaysImportedInRandomOrderTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public static function getOutputPrefix()
    {
        return "OneVisitor_SeveralDays_ImportedInRandomOrderTest";
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
        return array(
            // This should show 1 visit on 3 different days
            array('Live.getLastVisitsDetails', array(
                                    'idSite'                 => '1',
                                    'date'                   => self::$fixture->dateTime,
                                    'periods'                => 'month',
                                    'testSuffix'             => '_shouldShowOneVisit_InEachOfThreeDays',
                                    'otherRequestParameters' => array('hideColumns' => 'visitorId'),
                                    'keepLiveDates'          => true

            )),
        );
    }
}

OneVisitorSeveralDaysImportedInRandomOrderTest::$fixture = new VisitOverSeveralDaysImportedLogs();
