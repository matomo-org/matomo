<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin\tests\System;

use Piwik\Plugins\ExamplePlugin\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group ExamplePlugin
 * @group SimpleSystemTest
 * @group Plugins
 */
class SimpleSystemTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params): void
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = [
            'API.get',
            'Goals.getItemsSku',
        ];

        return [
            [
                $api,
                [
                    'idSite'     => 1,
                    'date'       => self::$fixture->dateTime,
                    'periods'    => ['day'],
                    'testSuffix' => '',
                ],
            ],
        ];
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

SimpleSystemTest::$fixture = new SimpleFixtureTrackFewVisits();
