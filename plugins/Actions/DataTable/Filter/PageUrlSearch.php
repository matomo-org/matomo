<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\DataTable\Filter;

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

        $searched = null;
        $isOnSiteHost = false;
        if ($site instanceof Site) {
            $idSite = $site->getId();
            $searched = $this->splitUrl((string) PageUrl::excludeQueryParametersFromUrl(trim($this->searchedUrl), $idSite));
            $isOnSiteHost = null !== $searched && in_array($searched['host'], $this->getSiteHosts($idSite), true);
        }

        $this->deleteRowsNotMatching($table, $searched, $isOnSiteHost);
    }

    /**
     * @param array{host: string, path: string}|null $searched
     * @return int The number of rows left.
     */
    private function deleteRowsNotMatching(DataTable $table, ?array $searched, bool $isOnSiteHost): int
    {
        foreach ($table->getRows() as $key => $row) {
            $subtable = $row->getSubtable();
            if ($subtable && $this->deleteRowsNotMatching($subtable, $searched, $isOnSiteHost) > 0) {
                continue;
            }

            $url = $row->getMetadata('url');
            $rowUrl = is_string($url) ? $this->splitUrl($url) : null;

            if (
                null === $searched
                || null === $rowUrl
                || $rowUrl['path'] !== $searched['path']
                || (!$isOnSiteHost && $rowUrl['host'] !== $searched['host'])
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
