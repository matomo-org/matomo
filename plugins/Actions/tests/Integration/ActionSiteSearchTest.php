<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Unit;

use Piwik\Plugins\Actions\Actions\ActionSiteSearch;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;

/**
 * @group Actions
 * @group SiteSearch
 * @group Plugins
 */
class ActionSiteSearchTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2010-01-01');
    }

    /**
     * @dataProvider getSiteSearchData
     */
    public function testSiteSearchDetection($website, $url, $expectedResult)
    {
        $action = new ActionSiteSearch(new Request(['idsite' => 1]));
        $this->assertEquals($expectedResult, ActionSiteSearch::detectSiteSearchFromUrl($website, parse_url($url)));
    }

    public function getSiteSearchData()
    {
        $defaultWebsite = [
            'idSite' => 1,
            'sitesearch_keyword_parameters' => ['q','k'],
            'sitesearch_category_parameters' => ['cat','cc']
        ];

        return [
            [$defaultWebsite, 'http://example.org/index.htm?q=keyword', ['http://example.org/index.htm', 'keyword', '', false]],
            [$defaultWebsite, 'http://example.org/index.htm#q=keyword&cat=test', ['http://example.org/index.htm', 'keyword', 'test', false]],
            [$defaultWebsite, 'http://example.org/index.htm#&q=keyword', ['http://example.org/index.htm', 'keyword', '', false]],
            [$defaultWebsite, 'http://example.org/index.htm#?cat=test&q=keyword', ['http://example.org/index.htm', 'keyword', 'test', false]],
            [$defaultWebsite, 'http://example.org/index.htm#?cat=test&q=keyword&otherparam=1', ['http://example.org/index.htm#otherparam=1', 'keyword', 'test', false]],
            [$defaultWebsite, 'http://example.org/index.htm?cat=test&otherparam=1&q=keyword', ['http://example.org/index.htm?otherparam=1', 'keyword', 'test', false]],
            [$defaultWebsite, 'http://example.org/index.htm#anchor?cat=test&otherparam=1&q=keyword', ['http://example.org/index.htm#anchor?otherparam=1', 'keyword', 'test', false]],
            [$defaultWebsite, 'http://example.org/index.htm?cat=test&otherparam=1&q=kw#?q=keyword', ['http://example.org/index.htm?otherparam=1', 'keyword', 'test', false]],
            [$defaultWebsite, 'http://example.org/index.htm?k=keyword&cc=cat', ['http://example.org/index.htm', 'keyword', 'cat', false]],
            [$defaultWebsite, '#?q=keyword', ['', 'keyword', '', false]],
            [$defaultWebsite, 'http://example.org/index.html?a=b#?&&&q=keyword', ['http://example.org/index.html?a=b', 'keyword', '', false]],
            [$defaultWebsite, 'http://example.org/#&?q=keyword', ['http://example.org/#&', 'keyword', '', false]],

            // some invalid/incorrect urls that aren't detected as site search
            [$defaultWebsite, 'http://example.org/index.html?a=b#?&&&?q=keyword', false],
            [$defaultWebsite, 'http://example.org/#&?var=val?q=keyword', false],
        ];
    }
}
