<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Tests\Fixtures\ThreeGoalsOnePageview;
use Piwik\Tests\Fixtures\TwoSitesEcommerceOrderWithItems;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group GoalsEcommerceTestTest
 * @group GoalsEcommerceTest
 * @group Plugins
 */
class GoalsEcommerceTest extends SystemTestCase
{
    /**
     * @var ThreeGoalsOnePageview
     */
    public static $fixture = null;

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
            ['Referrers.getReferrerType', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showSalesByReferrers',
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => 'ecommerceOrder',
                    'filter_show_goal_columns_process_goals' => 'ecommerceOrder',
                    'apiModule' => 'Referrers',
                    'apiAction' => 'getReferrerType',
                    'idGoal' => 'ecommerceOrder'
                ],
            ]],
            ['Actions.getPageUrls', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showSalesByPages',
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => 'ecommerceOrder',
                    'filter_show_goal_columns_process_goals' => 'ecommerceOrder',
                    'apiModule' => 'Actions',
                    'apiAction' => 'getPageUrls',
                    'idGoal' => 'ecommerceOrder'
                ],
            ]],
            ['Goals.getVisitsUntilConversion', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showSalesByVisits',
                'otherRequestParameters' => [
                    'apiModule' => 'Goals',
                    'apiAction' => 'getVisitsUntilConversion',
                    'idGoal' => 'ecommerceOrder'
                ],
            ]],
            ['UserCountry.getCountry', [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
                'testSuffix' => 'showSalesByUserCountry',
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => 'ecommerceOrder',
                    'filter_show_goal_columns_process_goals' => 'ecommerceOrder',
                    'apiModule' => 'UserCountry',
                    'apiAction' => 'getCountry',
                    'idGoal' => 'ecommerceOrder'
                ],
            ]],
        ];
    }

    public static function getOutputPrefix()
    {
        return 'goals_ecommerce';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

GoalsEcommerceTest::$fixture = new TwoSitesEcommerceOrderWithItems();
