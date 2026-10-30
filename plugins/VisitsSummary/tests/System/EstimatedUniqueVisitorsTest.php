<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary\tests\System;

use Piwik\Config;
use Piwik\Tests\Fixtures\VisitsOverSeveralDays;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group VisitsSummary
 * @group EstimatedUniqueVisitorsTest
 * @group Plugins
 */
class EstimatedUniqueVisitorsTest extends SystemTestCase
{
    /**
     * @var VisitsOverSeveralDays
     */
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Config::getInstance()->General['enable_processing_unique_visitors_estimated'] = 1;
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;

        return [
            ['VisitsSummary.get', [
                'idSite' => $idSite,
                'date' => '2010-12-14',
                'periods' => ['day', 'week', 'month', 'year'],
            ]],
            // spans the year boundary, so it merges days, weeks and a month
            ['VisitsSummary.get', [
                'idSite' => $idSite,
                'date' => '2010-12-10,2011-01-20',
                'periods' => ['range'],
            ]],
        ];
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

EstimatedUniqueVisitorsTest::$fixture = new VisitsOverSeveralDays();
