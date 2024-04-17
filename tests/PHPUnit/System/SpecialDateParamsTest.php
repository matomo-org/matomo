<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Fixtures\VisitsInCurrentYear;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group SpecialDateParams
 * @group Core
 */
class SpecialDateParamsTest extends SystemTestCase
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
        return [
            [
                ['VisitsSummary.getVisits', 'Actions.getPageTitles'],
                [
                    'idSite'       => self::$fixture->idSite,
                    'date'         => 'last week',
                    'periods'      => ['day'],
                    'testSuffix'   => '_lastweek',
                ],
            ],
            [
                ['VisitsSummary.getVisits', 'Actions.getPageTitles'],
                [
                    'idSite'       => self::$fixture->idSite,
                    'date'         => 'last month',
                    'periods'      => ['day'],
                    'testSuffix'   => '_lastmonth',
                ],
            ],
            [
                ['VisitsSummary.getVisits', 'Actions.getPageTitles'],
                [
                    'idSite'       => self::$fixture->idSite,
                    'date'         => 'lastyear',
                    'periods'      => ['day'],
                    'testSuffix'   => '_lastyear',
                ],
            ],
            [
                ['VisitsSummary.getVisits', 'Actions.getPageTitles'],
                [
                    'idSite'       => self::$fixture->idSite,
                    'date'         => 'last-year,lastWeek',
                    'periods'      => ['range'],
                    'testSuffix'   => '_range',
                ],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return 'specialDateParams';
    }
}

SpecialDateParamsTest::$fixture = new VisitsInCurrentYear();
