<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Fixtures\ExitPages;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\InvalidVisits;

/**
 * @group ExitPages
 * @group Core
 */
class ExitPageTest extends SystemTestCase
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
        yield 'check exit page urls and titles reports.' => [
            [
                'VisitsSummary.get',
                'Actions.getExitPageTitles',
                'Actions.getExitPageUrls',
            ],
            [
                'idSite' => 1,
                'date' => self::$fixture->dateTime,
                'period' => 'day',
            ],
        ];

        $segments = [
            // segment for page titles
            '_segmentTitleExit' => 'exitPageTitle==Exit%2BPage',
            '_segmentTitleExitSearch' => 'exitPageTitle==Exit%2BPage%2Bbefore%2BSite%2BSearch',
            '_segmentTitleExitSearch2' => 'exitPageTitle==Exit%2BPage%2Bwith%2BSite%2BSearch',
            '_segmentTitleExitContent' => 'exitPageTitle==Exit%2BPage%2Bbefore%2BContent%2BImpression',
            '_segmentTitleExitEvent' => 'exitPageTitle==Exit%2BPage%2Bbefore%2BEvent',
            '_segmentTitleExitOutlink' => 'exitPageTitle==Exit%2BPage%2Bbefore%2BDownload',
            '_segmentTitleExitDownload' => 'exitPageTitle==Exit%2BPage%2Bbefore%2BOutlink',
            '_segmentTitleExitGoal' => 'exitPageTitle==Exit%2BPage%2Bbefore%2BGoal',

            // segment for page urls
            '_segmentUrlExit' => 'exitPageUrl==' . urlencode('https://my.web.site/exit'),
            '_segmentUrlExitSearch' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_before_search'),
            '_segmentUrlExitSearch2' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_with_search'),
            '_segmentUrlExitContent' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_before_content'),
            '_segmentUrlExitEvent' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_before_event'),
            '_segmentUrlExitOutlink' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_before_outlink'),
            '_segmentUrlExitDownload' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_before_download'),
            '_segmentUrlExitGoal' => 'exitPageUrl==' . urlencode('https://my.web.site/exit_before_goal'),
        ];

        foreach ($segments as $suffix => $segment) {
            yield "check segment $segment works." => [
                [
                    'VisitsSummary.get',
                ],
                [
                    'idSite' => 1,
                    'date' => self::$fixture->dateTime,
                    'period' => 'day',
                    'segment' => $segment,
                    'testSuffix' => $suffix,
                ],
            ];
        }
    }

    public static function getOutputPrefix()
    {
        return 'exitPages';
    }
}

ExitPageTest::$fixture = new ExitPages();
