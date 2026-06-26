<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater;

use Piwik\Db;
use Piwik\Http;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\UpdateCheck\ReleaseChannel as BaseReleaseChannel;
use Piwik\Url;
use Piwik\Version;

abstract class ReleaseChannel extends BaseReleaseChannel
{
    public function getUrlToCheckForLatestAvailableVersion()
    {
        $parameters = array(
            'piwik_version'   => Version::VERSION,
            'php_version'     => PHP_VERSION,
            'mysql_version'   => Db::get()->getServerVersion(),
            'release_channel' => $this->getId(),
            'url'             => self::anonymiseUrl(Url::getCurrentUrlWithoutQueryString()),
        );

        $url = Client::getApiServiceUrl()
            . '/1.0/getLatestVersion/'
            . '?' . Http::buildQuery($parameters);

        return $url;
    }

    /**
     * Anonymises an installation URL for the update check API.
     *
     * Returns an empty string when the URL has no host, the host is `example.org`,
     * the host has no dot (e.g. `localhost`), or the host is any IP address (IPv4
     * or IPv6, public or private — IP-shaped installs are not counted). Otherwise
     * returns the SHA-256 of the lowercased host.
     *
     * The hash input is the host alone, not the full URL: `api_update_check.host`
     * on the API side has stored just the parsed host since 2016, so a backfilled
     * historical row and a fresh ping from the same install produce identical
     * hashes — cohort/churn queries spanning the cutover keep working.
     */
    public static function anonymiseUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $parts = @parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return '';
        }

        $host = strtolower($parts['host']);
        if (strlen($host) > 1 && $host[0] === '[' && substr($host, -1) === ']') {
            // strip IPv6 brackets so FILTER_VALIDATE_IP can detect the address
            $host = substr($host, 1, -1);
        }

        if (self::isExcludedHost($host)) {
            return '';
        }

        return hash('sha256', $host);
    }

    private static function isExcludedHost(string $host): bool
    {
        if ($host === '' || $host === 'example.org') {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        // Hostname: require at least one dot — `localhost` and similar bare
        // names are dev/test installs that have always been excluded.
        return strpos($host, '.') === false;
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        if (!empty($version)) {
            return sprintf('://builds.matomo.org/matomo-%s.zip', $version);
        }

        return '://builds.matomo.org/matomo.zip';
    }
}
