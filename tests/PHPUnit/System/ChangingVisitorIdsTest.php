<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitsWithChangingVisitorId;

/**
 * @group Core
 * @group ChangingVisitorIdsTest
 */
class ChangingVisitorIdsTest extends SystemTestCase
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
                [
                    'VisitsSummary.get',
                    'Actions.getPageUrls',
                    'Goals.get',
                    'Goals.getItemsSku',
                    'Events.getCategory',
                ],
                [
                    'idSite'  => self::$fixture->idSite,
                    'date'    => self::$fixture->date,
                    'periods' => ['day', 'month'],
                ],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return 'ChangingVisitorIds';
    }
}

ChangingVisitorIdsTest::$fixture = new VisitsWithChangingVisitorId();
