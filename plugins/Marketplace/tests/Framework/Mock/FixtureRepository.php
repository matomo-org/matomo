<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Framework\Mock;

use Piwik\Filesystem;

/**
 * Serves Marketplace HTTP responses from a directory of recorded fixtures.
 *
 * Used by Service::download() under PIWIK_TEST_MODE so tests never touch the
 * live Marketplace. Lookup is by canonical URL (path + significant query +
 * access_token from POST) via manifest.json. A miss throws so CI fails loudly
 * instead of leaking outbound requests.
 *
 * Manifest entries are either a string (filename, served as HTTP 200) or an
 * object `{"file": "name.json", "status": 400}` for non-2xx responses.
 *
 * Per-test overrides (createAccount response code, freeTrial success, etc.) are
 * registered via setOverride() and cleared between tests.
 */
class FixtureRepository
{
    private const NOISE_PARAMS = [
        'php',
        'mysql',
        'prefer_stable',
        'release_channel',
        'num_users',
        'num_websites',
    ];

    /** Major Matomo version the test environment normally runs against; piwik=5.x is treated as noise so we don't need per-minor fixtures. */
    private const CURRENT_PIWIK_MAJOR = '5';

    private const MARKETPLACE_HOSTS = [
        'plugins.matomo.org',
        'plugins.piwik.org',
        'themes.matomo.org',
        'themes.piwik.org',
    ];


    /**
     * @var string
     */
    private $directory;

    /**
     * @var array<string, string|array>|null
     */
    private $manifest;

    /**
     * @var array<string, string|array>
     */
    private static $overrides = [];

    public function __construct(?string $directory = null)
    {
        if ($directory === null) {
            $directory = PIWIK_INCLUDE_PATH . '/plugins/Marketplace/tests/resources';
        }

        $this->directory = rtrim($directory, '/');
    }

    /**
     * Override the fixture returned for a specific canonical key, for the lifetime
     * of the current test. Pass either a filename string or
     * `['file' => 'x.json', 'status' => 400]`.
     *
     * @param string $canonicalKey eg "/api/2.0/createAccount"
     * @param string|array $value
     */
    public static function setOverride(string $canonicalKey, $value): void
    {
        self::$overrides[$canonicalKey] = $value;
    }

    public static function clearOverrides(): void
    {
        self::$overrides = [];
    }

    /**
     * Attempt to serve a request from recorded fixtures.
     *
     * Manifest entry hit → serve the fixture. Manifest miss inside a known
     * marketplace host → throw, so CI fails loudly the first time a new
     * marketplace request appears, preventing silent live-network calls.
     *
     * @return string|array|true|null Same shape as Http::sendHttpRequestBy (true on destinationPath write), or null when the URL isn't ours.
     * @throws \Exception when no fixture matches a marketplace URL.
     */
    public function intercept(string $url, ?string $destinationPath, ?array $postData, bool $getExtendedInfo)
    {
        if (!$this->shouldIntercept($url)) {
            return null;
        }

        $key = $this->buildCanonicalKey($url, $postData);
        $entry = $this->lookup($key);

        if ($entry === null) {
            $message = sprintf(
                'No Marketplace fixture for URL "%s" (canonical key: "%s"). '
                . 'Add a hand-written entry to %s/manifest.json (see README.md).',
                $url,
                $key,
                $this->directory
            );
            // Application code sometimes catches \Exception broadly (e.g.
            // CorePluginsAdmin\Controller::createPluginsOrThemesView for offline
            // graceful degradation) and the test screenshot ends up rendering
            // without marketplace data without the trace appearing anywhere.
            // Log to stderr so CI can grep for it.
            error_log('[Marketplace FixtureRepository] ' . $message);
            throw new \Exception($message);
        }

        [$filename, $status] = $this->parseEntry($entry);

        // Defensive: the manifest is committed source so a path-traversal value
        // shouldn't reach here, but reject filenames that could escape the
        // fixtures directory so a malformed entry can't read arbitrary files.
        // Any directory separator is rejected, plus the bare "parent" name.
        if (
            $filename === '..'
            || strpos($filename, '/') !== false
            || strpos($filename, '\\') !== false
        ) {
            throw new \Exception(sprintf(
                'Marketplace manifest entry for "%s" contains an unsafe filename: "%s".',
                $key,
                $filename
            ));
        }

        $path = $this->directory . '/' . $filename;
        if (!file_exists($path)) {
            throw new \Exception(sprintf(
                'Marketplace fixture file "%s" referenced by manifest entry for "%s" is missing.',
                $path,
                $key
            ));
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new \Exception(sprintf(
                'Marketplace fixture "%s" could not be read (permissions or I/O error).',
                $path
            ));
        }

        if ($this->isJsonFixture($filename)) {
            // Compact the JSON to mimic what the live Marketplace API returns
            // (single-line). Lets fixtures stay pretty-printed on disk while
            // still satisfying tests that assert on raw response shape, e.g.
            // assertStringStartsWith('{"plugins"', $response).
            $decoded = json_decode($data, true);
            if ($decoded === null && trim($data) !== 'null') {
                throw new \Exception(sprintf(
                    'Marketplace fixture "%s" is not valid JSON: %s',
                    $path,
                    json_last_error_msg()
                ));
            }
            $data = json_encode($decoded, JSON_UNESCAPED_SLASHES);
        }

        if ($destinationPath !== null) {
            Filesystem::mkdir(@dirname($destinationPath));
            file_put_contents($destinationPath, $data);
            return true;
        }

        if ($getExtendedInfo) {
            return [
                'status' => $status,
                'headers' => [],
                'data' => $data,
            ];
        }

        return $data;
    }

    /**
     * Build a deterministic key from a URL + POST data:
     *   path + '?' + sorted query (significant params only, including access_token from POST).
     * Empty-string params and environment noise (piwik, php, mysql, ...) are dropped.
     */
    public function buildCanonicalKey(string $url, ?array $postData): string
    {
        $parsed = @parse_url($url);
        $path = $parsed['path'] ?? '';

        $params = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
        }

        if (!empty($postData['access_token'])) {
            $params['access_token'] = $postData['access_token'];
        }

        foreach (self::NOISE_PARAMS as $noise) {
            unset($params[$noise]);
        }

        // Strip piwik when it targets the current major (the common case) so we
        // don't need one fixture per Matomo minor version. Keep it for older
        // majors (e.g. LastForcedInstall pins piwik=4.16.2 which needs different data).
        if (isset($params['piwik']) && $this->isCurrentMatomoMajor((string) $params['piwik'])) {
            unset($params['piwik']);
        }

        foreach ($params as $name => $value) {
            if ($value === '' || $value === null) {
                unset($params[$name]);
            }
        }

        ksort($params);

        $query = http_build_query($params, '', '&');
        return $path . ($query !== '' ? '?' . $query : '');
    }

    /**
     * @return string|array|null
     */
    private function lookup(string $key)
    {
        if (array_key_exists($key, self::$overrides)) {
            return self::$overrides[$key];
        }

        $manifest = $this->loadManifest();
        return $manifest[$key] ?? null;
    }

    /**
     * @param string|array $entry
     * @return array{0: string, 1: int}
     */
    private function parseEntry($entry): array
    {
        if (is_array($entry)) {
            $filename = $entry['file'] ?? null;
            $status = isset($entry['status']) ? (int) $entry['status'] : 200;

            if (!is_string($filename) || $filename === '') {
                throw new \Exception('Marketplace fixture manifest entry missing "file".');
            }

            return [$filename, $status];
        }

        return [(string) $entry, 200];
    }

    /**
     * @return array<string, string|array>
     */
    private function loadManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $path = $this->directory . '/manifest.json';

        if (!file_exists($path)) {
            $this->manifest = [];
            return $this->manifest;
        }

        $contents = file_get_contents($path);
        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw new \Exception(sprintf(
                'Marketplace fixture manifest "%s" is not valid JSON.',
                $path
            ));
        }

        // Manifest keys must be canonical URLs starting with "/". The only
        // exception is documentation entries prefixed with "__" (e.g.
        // "__comment__"), which are stripped from the lookup table so they
        // can't accidentally match a request. Anything else is likely a typo
        // (missing leading slash) — throw so it surfaces at load time.
        foreach (array_keys($decoded) as $key) {
            if (!is_string($key) || $key === '') {
                throw new \Exception(sprintf(
                    'Marketplace fixture manifest "%s" has a non-string or empty key.',
                    $path
                ));
            }
            if (strpos($key, '/') === 0) {
                continue;
            }
            if (strpos($key, '__') === 0) {
                unset($decoded[$key]);
                continue;
            }
            throw new \Exception(sprintf(
                'Marketplace fixture manifest "%s" has an unrecognised key "%s" — URL keys must start with "/", documentation keys with "__".',
                $path,
                $key
            ));
        }

        $this->manifest = $decoded;
        return $this->manifest;
    }

    public function reloadManifest(): void
    {
        $this->manifest = null;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    private function isCurrentMatomoMajor(string $piwikVersion): bool
    {
        if ($piwikVersion === '') {
            return true;
        }
        return $piwikVersion === self::CURRENT_PIWIK_MAJOR
            || strpos($piwikVersion, self::CURRENT_PIWIK_MAJOR . '.') === 0;
    }

    private function shouldIntercept(string $url): bool
    {
        $host = strtolower((string) (@parse_url($url, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return false;
        }
        return in_array($host, self::MARKETPLACE_HOSTS, true);
    }

    private function isJsonFixture(string $filename): bool
    {
        return substr($filename, -5) === '.json';
    }
}
