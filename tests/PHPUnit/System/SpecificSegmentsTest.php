<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Fixtures\ManyVisitsWithMockLocationProvider;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group SpecificSegmentsTest
 * @group Core
 */
class SpecificSegmentsTest extends SystemTestCase
{
    /**
     * @var ManyVisitsWithMockLocationProvider
     */
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
        $manyNotContainsSegment = 'pageUrl!@15;pageUrl!@%252F9;pageUrl!@14;pageUrl!@12;pageUrl!@%252F7';
        return [
            [
                ['Actions.getPageUrls'],
                [
                    'idSite'       => self::$fixture->idSite,
                    'date'         => self::$fixture->dateTime,
                    'periods'      => ['day'],
                    'testSuffix'   => '_manyNotContains',
                    'segment' => $manyNotContainsSegment,
                ],
            ],
        ];
    }
}

SpecificSegmentsTest::$fixture = new ManyVisitsWithMockLocationProvider();
