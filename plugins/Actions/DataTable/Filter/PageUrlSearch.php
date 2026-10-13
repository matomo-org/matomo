<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Site;
use Piwik\Tracker\PageUrl;

/**
 * Searches a page URL report for a full URL, as copied from the browser, rather than for a pattern
 * in the labels, which hold only the path.
 *
 * A row matches when its `url` metadata is the searched URL. The scheme and a leading `www.` are
 * ignored, and the host may be any of the site's URLs: a row keeps only the URL viewed the most
 * among those sharing its path, so the host it holds may be another of the site's aliases. A URL
 * on a host that is not one of the site's URLs matches nothing.
 *
 * Folders have no URL of their own: they are kept when one of the pages they hold matches.
 */
class PageUrlSearch extends BaseFilter
{
    /**
     * @var string
     */
    private $searchedUrl;

    /**
     * @param DataTable $table
     * @param string $searchedUrl A full URL, see {@link isFullUrl()}.
     */
    public function __construct($table, string $searchedUrl)
    {
        parent::__construct($table);
        $this->searchedUrl = $searchedUrl;
    }

    public static function isFullUrl(string $search): bool
    {
        return preg_match('~^https?://[^/?#\s]+~i', trim($search)) === 1;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $site = $table->getMetadata('site');
        $idSite = $site instanceof Site ? $site->getId() : null;

        $siteHosts = $idSite ? $this->getSiteHosts($idSite) : [];
        $searched = $idSite ? $this->splitUrl(PageUrl::excludeQueryParametersFromUrl(trim($this->searchedUrl), $idSite)) : null;

        if (null === $searched || !in_array($searched['host'], $siteHosts, true)) {
            $searched = null;
        }

        $this->deleteRowsNotMatching($table, $searched, $siteHosts);
    }

    /**
     * @param array{host: string, path: string}|null $searched
     * @param list<string> $siteHosts
     * @return int The number of rows left.
     */
    private function deleteRowsNotMatching(DataTable $table, ?array $searched, array $siteHosts): int
    {
        foreach ($table->getRows() as $key => $row) {
            $subtable = $row->getSubtable();
            if ($subtable && $this->deleteRowsNotMatching($subtable, $searched, $siteHosts) > 0) {
                continue;
            }

            $url = $row->getMetadata('url');
            $rowUrl = is_string($url) ? $this->splitUrl($url) : null;

            if (
                null === $searched
                || null === $rowUrl
                || $rowUrl['path'] !== $searched['path']
                || !in_array($rowUrl['host'], $siteHosts, true)
            ) {
                $table->deleteRow($key);
            }
        }

        return $table->getRowsCount();
    }

    /**
     * @return array{host: string, path: string}|null The lower-cased host, without `www.`, and
     *                                                everything after it, `/` for nothing.
     */
    private function splitUrl(string $url): ?array
    {
        $withoutPrefix = PageUrl::normalizeUrl($url);
        if (null === $withoutPrefix['prefixId']) {
            return null;
        }

        $parts = preg_split('~(?=[/?#])~', $withoutPrefix['url'], 2);
        $host = mb_strtolower($parts[0]);
        $path = $parts[1] ?? '';

        if ($host === '') {
            return null;
        }

        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        return ['host' => $host, 'path' => $path];
    }

    /**
     * @return list<string> The hosts of the site's main URL and aliases, as {@link splitUrl()} returns them.
     */
    private function getSiteHosts(int $idSite): array
    {
        // the same cache entry PageUrl::shouldUseHttpsHost() reads, so one request asks for the URLs
        // once however many periods it covers
        $cache = Cache::getTransientCache();
        $cacheId = CacheId::siteAware('siteurls', [$idSite]);

        $siteUrls = $cache->fetch($cacheId);
        if (empty($siteUrls)) {
            $siteUrls = SitesManagerAPI::getInstance()->getSiteUrlsFromId($idSite);
            $cache->save($cacheId, $siteUrls);
        }

        $hosts = [];
        foreach ($siteUrls as $siteUrl) {
            $split = $this->splitUrl($siteUrl);
            if (null !== $split) {
                $hosts[] = $split['host'];
            }
        }

        return array_values(array_unique($hosts));
    }
}
