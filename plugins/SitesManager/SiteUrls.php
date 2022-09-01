<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Tracker\Request;

class SiteUrls
{
    private static $cacheId = 'allSiteUrlsPerSite';

    public static function clearSitesCache()
    {
        self::getCache()->delete(self::$cacheId);
    }

    /**
     * Groups all URLs by host, path and idsite.
     *
     * @param array $urls  An array containing URLs by idsite,
     *                     eg array(array($idSite = 1 => array('apache.piwik', 'apache2.piwik'), 2 => array(), ...))
     *                     as returned by {@link getAllCachedSiteUrls()} and {@link getAllSiteUrls}
     * @return array All urls grouped by host => path => idSites. Path having the most '/' will be listed first
     * array(
         'apache.piwik' => array(
             '/test/two' => $idsite = array(3),
             '/test' => $idsite = array(1),
             '/' => $idsite = array(2),
         ),
         'test.apache.piwik' => array(
             '/test/two' => $idsite = array(3),
             '/test' => $idsite = array(1),
             '/' => $idsite = array(2, 3),
         ),
       );
     */
    public function groupUrlsByHost($siteUrls)
    {
        if (empty($siteUrls)) {
            return [];
        }

        $allUrls = [];

        foreach ($siteUrls as $idSite => $urls) {
            $idSite = (int) $idSite;
            foreach ($urls as $url) {
                $this->addUrlByHost($allUrls, $idSite, $url);
            }
        }

        $this->sortUrlsByHost($allUrls);

        return $allUrls;
    }

    public function addUrlByHost(&$allUrls, $idSite, $url, $addPath = true)
    {
        $urlParsed = @parse_url($url);

        if ($urlParsed === false || !isset($urlParsed['host'])) {
            return;
        }

        $host = $this->toCanonicalHost($urlParsed['host']);
        $path = $this->getCanonicalPathFromParsedUrl($urlParsed);

        if (!isset($allUrls[$host])) {
            $allUrls[$host] = [];
        }

        if (!$addPath) {
            $path = '/';
        }

        if (!isset($allUrls[$host][$path])) {
            $allUrls[$host][$path] = [];
        }

        if (!in_array($idSite, $allUrls[$host][$path])) {
            $allUrls[$host][$path][] = $idSite;
        }
    }

    private function sortUrlsByHost(&$allUrls)
    {
        foreach ($allUrls as $host => $paths) {
            uksort($paths, [$this, 'sortByPathDepth']);
            $allUrls[$host] = $paths;
        }
    }

    public function getIdSitesMatchingUrl($parsedUrl, $urlsGroupedByHost)
    {
        if (empty($parsedUrl['host'])) {
            return null;
        }

        $urlHost = $this->toCanonicalHost($parsedUrl['host']);
        $urlPath = $this->getCanonicalPathFromParsedUrl($parsedUrl);

        // As wildcard subdomain might be allowed, for e.g. my.sub.example.org we need to check e.g.
        // - my.sub.example.org
        // - .my.sub.example.org
        // - .sub.example.org
        // - .example.org
        $hostsToCheck = [
            $urlHost,
            '.' . $urlHost,
        ];

        while (substr_count($urlHost, '.') >= 2) {
            $urlHost = substr($urlHost, strpos($urlHost, '.') + 1);
            $hostsToCheck[] = '.' . $urlHost;
        }

        foreach ($hostsToCheck as $host) {
            if (isset($urlsGroupedByHost[$host])) {
                $paths = $urlsGroupedByHost[$host];

                foreach ($paths as $path => $idSites) {
                    if (0 === strpos($urlPath, $path)) {
                        return $idSites;
                    }
                }

                if (isset($paths['/'])) {
                    return $paths['/'];
                }
            }
        }

        return null;
    }

    public function getPathMatchingUrl($parsedUrl, $urlsGroupedByHost)
    {
        if (empty($parsedUrl['host'])) {
            return null;
        }

        $urlHost = $this->toCanonicalHost($parsedUrl['host']);
        $urlPath = $this->getCanonicalPathFromParsedUrl($parsedUrl);

        if (isset($urlsGroupedByHost[$urlHost])) {
            $paths = $urlsGroupedByHost[$urlHost];

            foreach ($paths as $path => $idSites) {
                if (0 === strpos($urlPath, $path)) {
                    return $path;
                }
            }
        }
    }

    public function getAllCachedSiteUrls()
    {
        $cache    = $this->getCache();
        $siteUrls = $cache->fetch(self::$cacheId);

        if (empty($siteUrls)) {
            $siteUrls = $this->getAllSiteUrls();
            $cache->save(self::$cacheId, $siteUrls, 1800);
        }

        return $siteUrls;
    }

    public function getAllSiteUrls()
    {
        $model    = new Model();
        $siteUrls = $model->getAllKnownUrlsForAllSites();

        if (empty($siteUrls)) {
            return [];
        }

        $urls = [];
        foreach ($siteUrls as $siteUrl) {
            $siteId = (int) $siteUrl['idsite'];

            if (!isset($urls[$siteId])) {
                $urls[$siteId] = [];
            }

            $urls[$siteId][] = $siteUrl['url'];
        }

        return $urls;
    }

    private static function getCache()
    {
        return Cache::getLazyCache();
    }

    public function addRequestUrlToSiteUrls(&$allUrls, Request $request)
    {
        $idSite = $request->getIdSite();
        $url = $request->getParam('url');

        $this->addUrlByHost($allUrls, $idSite, $url, $addPath = false);
    }

    private function sortByPathDepth($pathA, $pathB)
    {
        // list first the paths with most '/' , and list path = '/' last
        $numSlashA = substr_count($pathA, '/');
        $numSlashB = substr_count($pathB, '/');

        if ($numSlashA === $numSlashB) {
            return -1 * strcmp($pathA, $pathB);
        }

        return $numSlashA > $numSlashB ? -1 : 1;
    }

    private function toCanonicalHost($host)
    {
        $host = mb_strtolower($host);
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }

        return $host;
    }

    private function getCanonicalPathFromParsedUrl($urlParsed)
    {
        $path = '/';

        if (isset($urlParsed['path'])) {
            $path = mb_strtolower($urlParsed['path']);
            if (!Common::stringEndsWith($path, '/')) {
                $path .= '/';
            }
        }

        return $path;
    }
}
