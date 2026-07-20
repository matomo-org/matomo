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
 * Registered as an `Http.sendHttpRequest` listener from
 * `plugins/Marketplace/config/test.php` so tests never touch the live
 * Marketplace. Lookup is by canonical URL (path + significant query +
 * access_token from the request body) via manifest.json.
 *
 * A miss on a known Marketplace host writes an `error_log` line prefixed
 * `[Marketplace FixtureRepository]` (deduplicated per canonical key) and
 * lets the real HTTP transport run. We deliberately do not raise a PHP
 * deprecation here — PHPUnit's `convertDeprecationsToExceptions` default
 * would otherwise turn that into a thrown exception in downstream plugin
 * CI runs that hit un-cached Marketplace URLs. The miss will graduate to a
 * hard throw once all in-tree and external plugin tests provide fixtures.
 *
 * Manifest entries are either a string (filename, served as HTTP 200) or an
 * object `{"file": "name.json", "status": 400}` for non-2xx responses.
 *
 * Plugins outside Marketplace can register their own manifest directories via
 * `registerManifestDirectory()`; later registrations win on key collisions.
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
     * Resolved manifest entries keyed by canonical URL. Each value carries the
     * source directory the entry came from so plugin-registered fixtures are
     * read from the plugin directory, not the Marketplace base directory.
     *
     * @var array<string, array{dir: string, entry: string|array}>|null
     */
    private $manifest;

    /**
     * @var array<string, string|array>
     */
    private static $overrides = [];

    /**
     * @var array<string> absolute directory paths, in registration order
     */
    private static $extraManifestDirs = [];

    /**
     * @var array<string, true> canonical keys we've already logged a miss for in this process
     */
    private static $loggedMisses = [];

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
     * Overrides resolve fixture filenames against the base directory of the
     * FixtureRepository instance handling the request.
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
     * Register an extra directory whose `manifest.json` is merged into the
     * lookup table. Intended for use from another plugin's `config/test.php`
     * so plugin tests can contribute their own Marketplace fixtures without
     * editing the Marketplace plugin.
     *
     * Later registrations override earlier entries for the same canonical key.
     * Fixture files are read from the directory the winning entry came from.
     */
    public static function registerManifestDirectory(string $absoluteDirectory): void
    {
        self::$extraManifestDirs[] = rtrim($absoluteDirectory, '/');
    }

    public static function clearRegisteredManifestDirectories(): void
    {
        self::$extraManifestDirs = [];
    }

    public static function clearLoggedMisses(): void
    {
        self::$loggedMisses = [];
    }

    /**
     * `Http.sendHttpRequest` listener.
     *
     * Sets the reference outputs to short-circuit the network call when a
     * fixture matches. Returns without touching anything when the URL is not a
     * Marketplace host, or on a soft miss (see class docblock).
     *
     * @param array $httpEventParams as built in Http::sendHttpRequestBy() — keys 'httpMethod', 'body', 'destinationPath', etc.
     * @param string|null $response Set on hit. Will be the fixture body, or '' when a destinationPath write succeeds.
     * @param int|null    $status   Set on hit to the manifest's status code (default 200).
     * @param array       $headers  Set on hit to an empty array; fixtures don't carry response headers.
     */
    public function respond(string $url, array $httpEventParams, &$response, &$status, &$headers): void
    {
        if (!$this->shouldIntercept($url)) {
            return;
        }

        $destinationPath = $httpEventParams['destinationPath'] ?? null;
        $postData = $this->extractPostData($httpEventParams['body'] ?? null);

        $key = $this->buildCanonicalKey($url, $postData);
        $resolved = $this->lookup($key);

        if ($resolved === null) {
            // TODO: switch back to throwing once all in-tree and plugin Marketplace tests provide fixtures. See PR #24624.
            $this->logMiss($url, $key);
            return;
        }

        [$filename, $statusCode] = $this->parseEntry($resolved['entry']);

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

        $path = $resolved['dir'] . '/' . $filename;
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
            $encoded = json_encode($decoded, JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new \Exception(sprintf(
                    'Marketplace fixture "%s" could not be re-encoded as JSON: %s',
                    $path,
                    json_last_error_msg()
                ));
            }
            $data = $encoded;
        }

        if ($destinationPath !== null) {
            Filesystem::mkdir(@dirname($destinationPath));
            file_put_contents($destinationPath, $data);
            // Sentinel: Http.php treats the event as handled when any of
            // response/status/headers is set and returns true on the
            // destinationPath branch when the file now exists on disk.
            $response = '';
            $status = $statusCode;
            $headers = [];
            return;
        }

        $response = $data;
        $status = $statusCode;
        $headers = [];
    }

    /**
     * Build a deterministic key from a URL + POST data:
     *   path + '?' + sorted query (significant params only, including access_token from POST).
     * Empty-string params and environment noise (piwik, php, mysql, ...) are dropped.
     */
    public function buildCanonicalKey(string $url, ?array $postData): string
    {
        // parse_url returns false for severely malformed URLs; coerce to [] so
        // the offset access below is well-defined (avoids PHP 8 "array offset
        // on value of type bool" warnings).
        $parsed = @parse_url($url) ?: [];
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
     * @param mixed $body
     * @return array<string, mixed>|null
     */
    private function extractPostData($body): ?array
    {
        if (is_array($body)) {
            return $body;
        }

        if (is_string($body) && $body !== '') {
            $parsed = [];
            parse_str($body, $parsed);
            return $parsed;
        }

        return null;
    }

    /**
     * @return array{dir: string, entry: string|array}|null
     */
    private function lookup(string $key)
    {
        if (array_key_exists($key, self::$overrides)) {
            return ['dir' => $this->directory, 'entry' => self::$overrides[$key]];
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

            if (!is_string($filename) || $filename === '') {
                throw new \Exception('Marketplace fixture manifest entry missing "file".');
            }

            if (!array_key_exists('status', $entry)) {
                $status = 200;
            } else {
                $rawStatus = $entry['status'];
                if (!is_int($rawStatus) || $rawStatus < 100 || $rawStatus > 599) {
                    throw new \Exception(sprintf(
                        'Marketplace fixture manifest entry has invalid HTTP status %s; expected integer in [100,599].',
                        var_export($rawStatus, true)
                    ));
                }
                $status = $rawStatus;
            }

            return [$filename, $status];
        }

        return [(string) $entry, 200];
    }

    /**
     * Build the lookup table from the base directory and any extra directories
     * registered via registerManifestDirectory(). Later directories override
     * earlier entries for the same canonical key.
     *
     * @return array<string, array{dir: string, entry: string|array}>
     */
    private function loadManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $merged = $this->loadManifestFile($this->directory);

        foreach (self::$extraManifestDirs as $extraDir) {
            $extraEntries = $this->loadManifestFile($extraDir);
            foreach ($extraEntries as $key => $value) {
                // Later registration wins on collision.
                $merged[$key] = $value;
            }
        }

        $this->manifest = $merged;
        return $this->manifest;
    }

    /**
     * @return array<string, array{dir: string, entry: string|array}>
     */
    private function loadManifestFile(string $directory): array
    {
        $path = $directory . '/manifest.json';

        if (!file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \Exception(sprintf(
                'Marketplace fixture manifest "%s" could not be read (permissions or I/O error).',
                $path
            ));
        }
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
        $entries = [];
        foreach ($decoded as $key => $value) {
            if (!is_string($key) || $key === '') {
                throw new \Exception(sprintf(
                    'Marketplace fixture manifest "%s" has a non-string or empty key.',
                    $path
                ));
            }
            if (strpos($key, '__') === 0) {
                continue;
            }
            if (strpos($key, '/') !== 0) {
                throw new \Exception(sprintf(
                    'Marketplace fixture manifest "%s" has an unrecognised key "%s" — URL keys must start with "/", documentation keys with "__".',
                    $path,
                    $key
                ));
            }
            $entries[$key] = ['dir' => $directory, 'entry' => $value];
        }

        return $entries;
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

    private function logMiss(string $url, string $key): void
    {
        if (isset(self::$loggedMisses[$key])) {
            return;
        }
        self::$loggedMisses[$key] = true;

        $message = sprintf(
            'No Marketplace fixture for URL "%s" (canonical key: "%s"). '
            . 'Add a hand-written entry to %s/manifest.json (see README.md). '
            . 'This will become a hard error in a future version (see PR #24624).',
            $url,
            $key,
            $this->directory
        );

        // stderr only — never trigger_error here. PHPUnit's
        // convertDeprecationsToExceptions default would otherwise turn this
        // into a thrown exception in any downstream plugin's CI that hits an
        // un-cached Marketplace URL.
        error_log('[Marketplace FixtureRepository] ' . $message);
    }
}
