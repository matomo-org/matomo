<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO;

use Piwik\Common;
use Piwik\Http;

/**
 * Client for Majestic SEO's HTTP API.
 *
 * Hides the HTTP request sending logic.
 */
class MajesticClient
{
    const API_BASE = 'http://simpleapi.majesticseo.com/sapi/';
    const API_KEY = 'ETHPYY'; // please only use this key within Piwik

    /**
     * Returns a URL that can be used to view all SEO data for a particular website.
     *
     * @param string $targetSiteUrl The URL of the website for whom SEO stats should be
     *                              accessible for.
     * @return string
     */
    public static function getLinkForUrl($targetSiteUrl)
    {
        $domain = @parse_url($targetSiteUrl, PHP_URL_HOST);
        return "http://www.majesticseo.com/reports/site-explorer/summary/$domain?IndexDataSource=F";
    }

    /**
     * Returns backlink statistics including the count of backlinks and count of
     * referrer domains (domains with backlinks).
     *
     * This method issues an HTTP request and waits for it to return.
     *
     * @param string $siteDomain The domain of the website to get stats for.
     * @param int $timeout The number of seconds to wait before aborting
     *                     the HTTP request.
     * @return array An array containing the backlink count and referrer
     *               domain count:
     *               array(
     *                   'backlink_count' => X,
     *                   'referrer_domains_count' => Y
     *               )
     *               If either stat is false, either the API returned an
     *               error, or the IP was blocked for this request.
     */
    public function getBacklinkStats($siteDomain, $timeout = 300)
    {
        $apiUrl = $this->getApiUrl($method = 'GetBacklinkStats', $args = array(
            'items' => '1',
            'item0' => $siteDomain
        ));
        $apiResponse = Http::sendHttpRequest($apiUrl, $timeout);

        $result = array(
            'backlink_count'         => false,
            'referrer_domains_count' => false
        );

        $apiResponse = Common::json_decode($apiResponse, $assoc = true);
        if (!empty($apiResponse)
            && !empty($apiResponse['Data'])
        ) {
            $siteSeoStats = reset($apiResponse['Data']);

            if (isset($siteSeoStats['ExtBackLinks'])
                && $siteSeoStats['ExtBackLinks'] !== -1
            ) {
                $result['backlink_count'] = $siteSeoStats['ExtBackLinks'];
            }

            if (isset($siteSeoStats['RefDomains'])
                && $siteSeoStats['RefDomains'] !== -1
            ) {
                $result['referrer_domains_count'] = $siteSeoStats['RefDomains'];
            }
        }

        return $result;
    }

    private function getApiUrl($method, $args = array())
    {
        $args['sak'] = self::API_KEY;

        $queryString = http_build_query($args);
        return self::API_BASE . $method . '?' . $queryString;
    }
}
