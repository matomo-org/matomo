<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\System;

use Piwik\Piwik;
use Piwik\Plugins\Ecommerce\tests\Fixtures\AbandonedCartWithoutConversions;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class AbandonedCartWithoutConversionsTest extends SystemTestCase
{
    /**
     * @var AbandonedCartWithoutConversions
     */
    public static $fixture;

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite   = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $api = ['Goals'];
        $goalItemApi = ['Goals.getItemsSku', 'Goals.getItemsName', 'Goals.getItemsCategory'];

        return [
            [
                $api, [
                    'idSite' => $idSite,
                    'date' => $dateTime,
                    'periods' => ['day', 'week'],
                    'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART,
                ],
            ],
            [
                $goalItemApi, [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'periods'                => ['day', 'week'],
                    'testSuffix'             => '_AbandonedCarts',
                    'otherRequestParameters' => [
                        'abandonedCarts' => 1,
                    ],
                ],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return 'abandonedCartWithoutConversions';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

AbandonedCartWithoutConversionsTest::$fixture = new AbandonedCartWithoutConversions();
