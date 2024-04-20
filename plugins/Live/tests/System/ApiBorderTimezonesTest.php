<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\System;

use Piwik\Plugins\Live\tests\Fixtures\TwoSitesWithBorderTimezones;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Live
 * @group ApiTest
 * @group Api
 * @group Plugins
 *
 * @group BorderTimezones
 */
class ApiBorderTimezonesTest extends SystemTestCase
{
    /**
     * @var TwoSitesWithBorderTimezones
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        // results should contain
        // - first visit: January 31
        // - last visit: February 1
        $this->runApiTests($api, $params);
    }

    /**
     * @return iterable<string, array>
     */
    public function getApiForTesting(): iterable
    {
        yield 'UTC-12' => [
            ['Live.getVisitorProfile'],
            [
                'idSite'            => self::$fixture->idSiteUtcMinus,
                'date'              => self::$fixture->dateTime,
                'periods'           => ['day'],
                'testSuffix'        => 'utcMinus',
                'keepLiveDates'     => true,
                'xmlFieldsToRemove' => ['daysAgo'],
            ],
        ];

        yield 'UTC+14' => [
            ['Live.getVisitorProfile'],
            [
                'idSite'            => self::$fixture->idSiteUtcPlus,
                'date'              => self::$fixture->dateTime,
                'periods'           => ['day'],
                'testSuffix'        => 'utcPlus',
                'keepLiveDates'     => true,
                'xmlFieldsToRemove' => ['daysAgo'],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

ApiBorderTimezonesTest::$fixture = new TwoSitesWithBorderTimezones();
