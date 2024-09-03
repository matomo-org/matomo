<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests;

use Piwik\Plugins\CustomDimensions\API;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class SegmentTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $idSite = Fixture::createWebsite('2012-02-03');
        API::getInstance()->configureNewCustomDimension($idSite, 'test dim', 'visit', 1);
    }

    public function testSegmentCanSeeCustomDimensionSegments()
    {
        $select = 'log_visit.idvisit';
        $from = 'log_visit';

        $segmentStr = 'dimension1==5';
        $segment = new Segment($segmentStr, [1]);

        /** @var array $query */
        $query = $segment->getSelectQuery($select, $from);
        $query['sql'] = trim(preg_replace('/\s+/', ' ', $query['sql']));

        $expectedQuery = [
            'sql' => 'SELECT log_visit.idvisit FROM log_visit AS log_visit WHERE log_visit.custom_dimension_1 = ?',
            'bind' => [
                5,
            ],
        ];
        $this->assertEquals($expectedQuery, $query);
    }
}
