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
     * Returns an empty string when the URL has no host, when the host is
     * IP-shaped (any IPv4/IPv6, public or private), or when the host is one
     * we don't count as a real install: bare no-dot names (`localhost`),
     * `example.org` (RFC 2606 documentation TLD; the API side has excluded
     * it since forever, so we mirror it here for continuity), and hosts
     * ending in a reserved / dev-only suffix (see
     * {@see self::EXCLUDED_HOST_SUFFIXES}). Otherwise returns the SHA-256
     * of the lowercased host.
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

    /**
     * Host suffixes that identify non-production installs. Kept as a single
     * list rather than per-RFC scatter so the client and api.matomo.org
     * fallback stay in lock-step — the two sides MUST agree, or legacy
     * clients (which send the raw URL and are hashed server-side) end up
     * with different exclusion decisions than fresh clients.
     */
    private const EXCLUDED_HOST_SUFFIXES = [
        // RFC 2606 / 6761 — reserved for documentation & testing.
        '.test',
        '.example',
        '.invalid',
        '.localhost',
        // RFC 6762 — mDNS "local" names (default on macOS and many home LANs).
        '.local',
        // RFC 8375 — residential home network namespace.
        '.home.arpa',
        // RFC 7686 — Tor hidden services.
        '.onion',
        // RFC 9476 — alternative resolution namespaces.
        '.alt',
        // ICANN reservation (2024) — private-use internal DNS.
        '.internal',
        // DDEV — the local-dev tool the Matomo team uses; hostnames like
        // matomo.ddev.site resolve to 127.0.0.1. Registered domain, so no
        // RFC/PSL check catches it — we exclude explicitly.
        '.ddev.site',
    ];

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
        if (strpos($host, '.') === false) {
            return true;
        }

        foreach (self::EXCLUDED_HOST_SUFFIXES as $suffix) {
            if (substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
        }

        return false;
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        if (!empty($version)) {
            return sprintf('://builds.matomo.org/matomo-%s.zip', $version);
        }

        return '://builds.matomo.org/matomo.zip';
    }
}
