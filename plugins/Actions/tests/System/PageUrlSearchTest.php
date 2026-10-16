<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * A full URL pasted into the search of a page URL report, which the labels, holding only the path,
 * never match.
 *
 * @group Actions
 * @group PageUrlSearchTest
 */
class PageUrlSearchTest extends IntegrationTestCase
{
    private const DATE = '2015-03-04';

    /**
     * @var int
     */
    private static $idSite;

    /**
     * Tracks once for the whole class: the tables are restored before each test.
     */
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        self::$idSite = Fixture::createWebsite(
            self::DATE . ' 00:00:00',
            $ecommerce = 0,
            $siteName = false,
            ['http://example.com', 'https://shop.example.org'],
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $timezone = null,
            $type = null,
            $excludeUnknownUrls = 0,
            $excludedParameters = 'sessionid'
        );

        self::trackPageViews([
            'http://example.com/products/item?id=123',
            'http://example.com/products/item?id=124',
            // the same last part of the path, under another folder
            'http://example.com/blog/item?id=123',
            'https://shop.example.org/cart/view',
            'http://example.com/account/login?sessionid=abc',
            // a host the site's URLs do not list
            'http://preview.example.com/pricing',
            'http://example.com:8080/staging/page',
        ]);
    }

    /**
     * @dataProvider getSearches
     * @param list<string> $expectedPages
     */
    public function testFindsThePageAFullUrlWasTrackedAs(string $search, bool $flat, array $expectedPages)
    {
        $this->assertEquals($expectedPages, $this->searchPageUrls($search, $flat));
    }

    public function getSearches(): iterable
    {
        foreach ([false, true] as $flat) {
            $view = $flat ? 'flat' : 'hierarchical';

            yield "a page on the site's main URL, $view" => [
                'https://example.com/products/item?id=123',
                $flat,
                ['/products/item?id=123'],
            ];

            yield "the scheme and www. are ignored, $view" => [
                'http://www.example.com/products/item?id=123',
                $flat,
                ['/products/item?id=123'],
            ];

            yield "a page on an alias of the site, $view" => [
                'https://shop.example.org/cart/view',
                $flat,
                ['/cart/view'],
            ];

            yield "the main URL finds a page tracked on an alias, $view" => [
                'https://example.com/cart/view',
                $flat,
                ['/cart/view'],
            ];

            yield "the main URL finds a page tracked on a host the site's URLs do not list, $view" => [
                'https://example.com/pricing',
                $flat,
                ['/pricing'],
            ];

            yield "the URL a page holds, on a host the site's URLs do not list, $view" => [
                'http://preview.example.com/pricing',
                $flat,
                ['/pricing'],
            ];

            yield "a host with a port, $view" => [
                'http://example.com:8080/staging/page',
                $flat,
                ['/staging/page'],
            ];

            yield "an upper-case scheme and host, $view" => [
                'HTTPS://WWW.EXAMPLE.COM/products/item?id=123',
                $flat,
                ['/products/item?id=123'],
            ];

            yield "a host that is not one of the site's URLs, $view" => [
                'https://elsewhere.example.net/products/item?id=123',
                $flat,
                [],
            ];

            yield "a parameter excluded from tracking is ignored, $view" => [
                'http://example.com/account/login?sessionid=xyz',
                $flat,
                ['/account/login'],
            ];

            yield "a URL tracked for none of the pages, $view" => [
                'https://example.com/products/item?id=999',
                $flat,
                [],
            ];
        }
    }

    public function testKeepsSearchingLabelsForTextThatIsNotAFullUrl()
    {
        $this->assertEquals(
            ['/blog/item?id=123', '/products/item?id=123', '/products/item?id=124'],
            $this->searchPageUrls('item', true)
        );
    }

    public function testFindsThePageInTheEntryAndExitPageReports()
    {
        foreach (['Actions.getEntryPageUrls', 'Actions.getExitPageUrls'] as $method) {
            $this->assertEquals(
                ['/cart/view'],
                $this->searchPageUrls('https://shop.example.org/cart/view', true, $method),
                $method
            );
        }
    }

    public function testComparesTheSearchedUrlAsIsWhenItCannotBeNormalized()
    {
        // archive first, then make the site's tracker cache, which the normalization reads, fail to build
        $this->searchPageUrls('item', true);
        TrackerCache::deleteCacheWebsiteAttributes(self::$idSite);
        Piwik::addAction('Tracker.Cache.getSiteAttributes', function () {
            throw new \Exception('The tracker cache cannot be built');
        });

        $this->assertEquals(
            ['/products/item?id=123'],
            $this->searchPageUrls('https://example.com/products/item?id=123', true)
        );
    }

    /**
     * @param list<string> $urls Each one tracked as its own visit.
     */
    private static function trackPageViews(array $urls): void
    {
        foreach ($urls as $index => $url) {
            $tracker = Fixture::getTracker(self::$idSite, sprintf('%s %02d:00:00', self::DATE, $index), $defaultInit = true);
            $tracker->setUrl($url);
            Fixture::checkResponse($tracker->doTrackPageView('page ' . $index));
        }
    }

    /**
     * @return list<string> The path of each page the search finds, sorted.
     */
    private function searchPageUrls(string $search, bool $flat, string $method = 'Actions.getPageUrls'): array
    {
        /** @var DataTable $table */
        $table = Request::processRequest($method, [
            'idSite' => self::$idSite,
            'period' => 'day',
            'date' => self::DATE,
            'flat' => $flat ? '1' : '0',
            'expanded' => $flat ? '0' : '1',
            'filter_column_recursive' => 'label',
            'filter_pattern_recursive' => $search,
            'filter_limit' => '-1',
        ]);

        $pages = $this->collectPagePaths($table, '');
        sort($pages);

        return $pages;
    }

    /**
     * @return list<string>
     */
    private function collectPagePaths(DataTable $table, string $folder): array
    {
        $pages = [];
        foreach ($table->getRows() as $row) {
            $label = (string) $row->getColumn('label');
            $subtable = $row->getSubtable();

            if ($subtable) {
                $pages = array_merge($pages, $this->collectPagePaths($subtable, $folder . '/' . $label));
            } else {
                // a page's label starts with `/` in both views, see ArchivingHelper::URL_ACTION_LEAF_MARKER
                $pages[] = $folder . $label;
            }
        }

        return $pages;
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
