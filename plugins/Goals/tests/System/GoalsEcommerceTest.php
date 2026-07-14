<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Tests\Fixtures\ThreeGoalsOnePageview;
use Piwik\Tests\Framework\Fixture;
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

    public function testSalesByReferrerTypeSeparatesAIAssistantAndCampaignEcommerceOrders(): void
    {
        $this->trackAIAssistantEcommerceOrder('2011-06-03 10:00:00', 'ai-campaign-order-1', 100, 'http://chatgpt.com');
        $this->trackAIAssistantEcommerceOrder('2011-06-05 10:00:00', 'ai-campaign-order-2', 200, 'http://chatgpt.com');
        $this->trackAIAssistantEcommerceOrder('2011-06-05 11:00:00', 'ai-campaign-order-3', 40, 'http://chatgpt.com');
        $this->trackAIAssistantEcommerceOrder('2011-06-03 11:00:00', 'ai-attributed-order-1', 50);
        $this->trackAIAssistantEcommerceOrder('2011-06-05 12:00:00', 'ai-attributed-order-2', 60);

        $report = Request::processRequest('Referrers.getReferrerType', [
            'idSite' => self::$fixture->idSite,
            'period' => 'month',
            'date' => '2011-06-01',
            'idGoal' => 'ecommerceOrder',
            // Load-bearing request params: the two goal filters materialise the
            // goal_ecommerceOrder_* columns asserted below, and _setReferrerTypeLabel=0
            // keeps the row labels as the numeric referrer-type ids matched by the
            // Common::REFERRER_TYPE_* constants in assertSalesByReferrerType(). Do not drop them.
            'filter_update_columns_when_show_all_goals' => 'ecommerceOrder',
            'filter_show_goal_columns_process_goals' => 'ecommerceOrder',
            'apiModule' => 'Referrers',
            'apiAction' => 'getReferrerType',
            '_setReferrerTypeLabel' => 0,
            'format_metrics' => 0,
        ]);

        $this->assertInstanceOf(DataTable::class, $report);
        $this->assertSalesByReferrerType($report);
    }

    private function assertSalesByReferrerType(DataTable $report): void
    {
        $campaignRow = $report->getRowFromLabel(Common::REFERRER_TYPE_CAMPAIGN);
        $aiAssistantRow = $report->getRowFromLabel(Common::REFERRER_TYPE_AI_ASSISTANT);

        $this->assertNotFalse($campaignRow);
        $this->assertNotFalse($aiAssistantRow);

        $this->assertSame(3, (int) $campaignRow->getColumn('goal_ecommerceOrder_nb_conversions'), 'Campaign ecommerce orders');
        $this->assertSame(340.0, (float) $campaignRow->getColumn('goal_ecommerceOrder_revenue'), 'Campaign ecommerce revenue');

        $this->assertSame(2, (int) $aiAssistantRow->getColumn('goal_ecommerceOrder_nb_conversions'), 'AI Assistant ecommerce orders');
        $this->assertSame(110.0, (float) $aiAssistantRow->getColumn('goal_ecommerceOrder_revenue'), 'AI Assistant ecommerce revenue');
    }

    private function trackAIAssistantEcommerceOrder(string $dateTime, string $orderId, float $revenue, ?string $campaignName = null): void
    {
        $tracker = Fixture::getTracker(self::$fixture->idSite, $dateTime, $defaultInit = true);
        $tracker->setNewVisitorId();
        $tracker->setUrl('http://example.org/landing?utm_source=chatgpt.com');
        Fixture::checkResponse($tracker->doTrackPageView('AI landing page'));

        $tracker->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.1)->getDatetime());

        if ($campaignName !== null) {
            $tracker->setCustomTrackingParameter('_rcn', $campaignName);
        }

        Fixture::checkResponse($tracker->doTrackEcommerceOrder($orderId, $revenue));
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
                    'idGoal' => 'ecommerceOrder',
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
                    'idGoal' => 'ecommerceOrder',
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
                    'idGoal' => 'ecommerceOrder',
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
                    'idGoal' => 'ecommerceOrder',
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
