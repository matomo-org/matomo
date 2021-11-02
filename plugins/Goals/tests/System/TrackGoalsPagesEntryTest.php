<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomePageGoalVisitsWithConversions;

/**
 * Tests API methods with goals that do and don't allow multiple
 * conversions per visit.
 *
 * @group TrackGoalsPagesEntryTest
 * @group TrackGoalsPages
 * @group Plugins
 */
class TrackGoalsPagesEntryTest extends SystemTestCase
{
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
        $apiToCall = array(
            'Goals.getPagesEntry',
            'Goals.getPagesEntryTitles',
        );

        return array(
            [$apiToCall,
                [
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'idGoal' => 1,
                'otherRequestParameters' => [
                    'filter_update_columns_when_show_all_goals' => 1
                    ]
                ],
            ],

        );
    }

    public static function getOutputPrefix()
    {
        return 'trackGoals_pagesEntry';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TrackGoalsPagesEntryTest::$fixture = new SomePageGoalVisitsWithConversions();