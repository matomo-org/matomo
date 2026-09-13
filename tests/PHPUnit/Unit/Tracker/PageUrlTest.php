<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Config;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\PageUrl;

/**
 * @group Core
 * @group PageUrlTest
 */
class PageUrlTest extends UnitTestCase
{
    public function testGetQueryParametersToExcludeDoesNotLowercaseRegexMetacharacters()
    {
        $this->setExcludedQueryParameters('/^ad_\D+$/,/^ad_\Q.x\E$/,SessionID');

        $excluded = PageUrl::getQueryParametersToExclude(0);

        $this->assertContains('/^ad_\D+$/', $excluded);
        $this->assertNotContains('/^ad_\d+$/', $excluded);
        $this->assertContains('/^ad_\Q.x\E$/', $excluded);
        $this->assertContains('sessionid', $excluded);
        $this->assertNotContains('SessionID', $excluded);
    }

    /**
     * @dataProvider getUppercaseRegexMetacharacterUrls
     */
    public function testExcludeQueryParametersFromUrlPreservesUppercaseRegexMetacharacters($excludedQueryParameters, $url, $expectedUrl)
    {
        $this->setExcludedQueryParameters($excludedQueryParameters);

        $this->assertSame($expectedUrl, PageUrl::excludeQueryParametersFromUrl($url, 0));
    }

    public function getUppercaseRegexMetacharacterUrls()
    {
        return [
            'non-digit pattern excludes letters' => [
                '/^ad_\D+$/',
                'http://example.com/index?ad_abc=1&keep=1',
                'http://example.com/index?keep=1',
            ],
            'non-digit pattern keeps digits' => [
                '/^ad_\D+$/',
                'http://example.com/index?ad_123=1&keep=1',
                'http://example.com/index?ad_123=1&keep=1',
            ],
            'quoted metacharacters stay quoted' => [
                '/^ad_\Q.x\E$/',
                'http://example.com/index?ad_.x=1&keep=1',
                'http://example.com/index?keep=1',
            ],
            'shipped UI example still matches session params' => [
                '/^sess.*|.*[dD]ate$/',
                'http://example.com/index?sessionid=abc&keep=1',
                'http://example.com/index?keep=1',
            ],
            'shipped UI example still matches Date after name is lowercased' => [
                '/^sess.*|.*[dD]ate$/',
                'http://example.com/index?Date=2026-01-01&keep=1',
                'http://example.com/index?keep=1',
            ],
            'literal names remain case-insensitive' => [
                'SessionID',
                'http://example.com/index?sessionid=abc&keep=1',
                'http://example.com/index?keep=1',
            ],
        ];
    }

    private function setExcludedQueryParameters($parameters)
    {
        $section = Config::getInstance()->Tracker;
        $section['url_query_parameter_to_exclude_from_url'] = $parameters;
        Config::getInstance()->Tracker = $section;
    }
}
