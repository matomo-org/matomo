<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\Site;
use Piwik\Tracker\PageUrl;

/**
 * Searches a page URL report for a full URL, as copied from the browser, rather than for a pattern
 * in the labels, which hold only the path.
 *
 * A row matches when its `url` metadata has the searched path and query, the scheme and a leading
 * `www.` being ignored. Its host is not compared: a row gathers every host the path was viewed on
 * and keeps only the URL viewed the most, so the host it holds may be an alias, or a host the
 * site's URLs do not list. The searched URL has to be on one of the site's URLs, or on the host
 * the row holds.
 *
 * Like the archiver, a path ending with `/` is compared with `action_default_name` appended: `/docs/`
 * and `/docs/index` are a single row, which holds only one of the two URLs.
 *
 * The URL is searched both without the parameters the site excludes today, and as pasted: a row
 * holds its URL as tracked, before a parameter was excluded.
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
     * @var string
     */
    private $defaultActionName = '';

    /**
     * @param DataTable $table
     * @param string $searchedUrl A full URL, see {@link isFullUrl()}, sanitized as the request holds it.
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
        if (!$site instanceof Site) {
            // without its site, the URL cannot be normalized like the tracker did: search the labels instead
            $table->filter('PatternRecursive', ['label', $this->searchedUrl]);
            return;
        }

        $this->defaultActionName = (string) Config::getInstance()->General['action_default_name'];

        $idSite = $site->getId();
        $urls = [PageUrl::cleanupUrl($this->searchedUrl)];
        try {
            $urls[] = (string) PageUrl::excludeQueryParametersFromUrl(trim($this->searchedUrl), $idSite);
        } catch (\Exception $e) {
            // for example when the site's tracker cache fails to build: the URL is searched as pasted only
        }

        $siteHosts = $this->getSiteHosts($idSite);
        $searchedUrls = [];
        foreach (array_unique($urls) as $url) {
            $searched = $this->splitUrl($url);
            if (null !== $searched) {
                $searched['isOnSiteHost'] = in_array($searched['host'], $siteHosts, true);
                $searchedUrls[] = $searched;
            }
        }

        $this->deleteRowsNotMatching($table, $searchedUrls);
    }

    /**
     * @param list<array{host: string, path: string, isOnSiteHost: bool}> $searchedUrls
     * @return int The number of rows left.
     */
    private function deleteRowsNotMatching(DataTable $table, array $searchedUrls): int
    {
        foreach ($table->getRows() as $key => $row) {
            $subtable = $row->getSubtable();
            if ($subtable && $this->deleteRowsNotMatching($subtable, $searchedUrls) > 0) {
                continue;
            }

            $url = $row->getMetadata('url');
            $rowUrl = is_string($url) ? $this->splitUrl($url) : null;

            if (null === $rowUrl || !$this->isMatchingAny($rowUrl, $searchedUrls)) {
                $table->deleteRow($key);
            }
        }

        return $table->getRowsCount();
    }

    /**
     * @param array{host: string, path: string} $rowUrl
     * @param list<array{host: string, path: string, isOnSiteHost: bool}> $searchedUrls
     */
    private function isMatchingAny(array $rowUrl, array $searchedUrls): bool
    {
        foreach ($searchedUrls as $searched) {
            // the path and query are compared as tracked, neither decoded nor reordered: `/café` does
            // not find a page tracked as `/caf%C3%A9`, nor `?b=2&a=1` one tracked as `?a=1&b=2`
            if (
                $rowUrl['path'] === $searched['path']
                && ($searched['isOnSiteHost'] || $rowUrl['host'] === $searched['host'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{host: string, path: string}|null The lower-cased host, without `www.`, and
     *                                                everything after it, `/` for nothing, with
     *                                                `action_default_name` after a trailing `/`.
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

        // like ArchivingHelper::parseNameFromPageUrl(), which looks before the fragment, query included
        $fragmentStart = strcspn($path, '#');
        if ($path[$fragmentStart - 1] === '/') {
            $path = substr_replace($path, $this->defaultActionName, $fragmentStart, 0);
        }

        return ['host' => $host, 'path' => $path];
    }

    /**
     * @return list<string> The hosts of the site's main URL and aliases, as {@link splitUrl()} returns them.
     */
    private function getSiteHosts(int $idSite): array
    {
        $hosts = [];
        foreach (PageUrl::getSiteUrls($idSite) as $siteUrl) {
            $split = $this->splitUrl($siteUrl);
            if (null !== $split) {
                $hosts[] = $split['host'];
            }
        }

        return array_values(array_unique($hosts));
    }
}
