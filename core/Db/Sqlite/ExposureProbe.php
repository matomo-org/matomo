<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Sqlite;

use Composer\CaBundle\CaBundle;
use PDO;
use Piwik\Config\GeneralConfig;
use Piwik\Http;

/**
 * Checks over HTTP that the web server doesn't serve a SQLite database file.
 *
 * The database file, its `-wal` and `-shm` files and a canary written next to them are requested. Committed
 * data sits in the `-wal` file, and a deny rule on `*.sqlite` doesn't cover `*.sqlite-wal`, so every file
 * is checked. Each URL is:
 *
 * - protected: HTTP 401, 403 or 404;
 * - exposed: a 2xx response that starts as the file does (its format's signature), or that is the canary;
 * - inconclusive: anything else, e.g. a failed request, a 5xx, a redirect or a catch-all page.
 *
 * The location is protected only if every URL is, and exposed if any URL is. Nothing counts as protection
 * unless Matomo's own `matomo.js` is first served at the URL the same mapping gives it, so a wrong base URL
 * or a catch-all 404 can't pass for a deny rule. Redirects are not followed, and at most a few bytes of a
 * response are read. The database files themselves are never opened here except through SQLite: closing
 * another descriptor of a database file would release this process's SQLite locks on it.
 */
class ExposureProbe
{
    public const PROTECTED = 'protected';
    public const EXPOSED = 'exposed';
    public const INCONCLUSIVE = 'inconclusive';

    /**
     * The file is neither under the Matomo directory nor under the document root, so no URL serves it.
     */
    public const OUTSIDE_WEB_ROOT = 'outside_web_root';

    /**
     * Nothing could be checked: Matomo's own `matomo.js` isn't served where expected, or the file is outside the
     * Matomo directory and the document root is unknown.
     */
    public const UNVERIFIED = 'unverified';

    /**
     * The most bytes read from a response.
     */
    private const MAX_BODY_BYTES = 64;

    private const TIMEOUT_SECONDS = 5;

    /**
     * The WAL-index format version, at the start of a `-shm` file in the host's byte order.
     */
    private const WAL_INDEX_VERSION = 3007000;

    /**
     * @var callable(string): array{status: int, body: string}
     */
    private $fetch;

    /**
     * @param null|callable(string): array{status: int, body: string} $fetch Requests a URL without following
     *        redirects and returns its status and at most the first bytes of its body; throws on a transport failure.
     */
    public function __construct(?callable $fetch = null)
    {
        $this->fetch = $fetch ?? [self::class, 'fetch'];
    }

    /**
     * @param string $file the database file; it must exist
     * @param string $baseUrl the URL of the Matomo directory
     * @param string|null $documentRoot the web server's document root, when known
     * @return array{status: string, urls: array<string, string>} the combined result, and each URL's
     */
    public function probe(string $file, string $baseUrl, ?string $documentRoot = null): array
    {
        $fileUrls = $this->getUrls($file, $baseUrl, $documentRoot);
        if (empty($fileUrls)) {
            // under an unknown document root, the file could still be served
            $isRootKnown = $documentRoot !== null && $documentRoot !== '' && realpath($documentRoot) !== false;
            return ['status' => $isRootKnown ? self::OUTSIDE_WEB_ROOT : self::UNVERIFIED, 'urls' => []];
        }

        $results = [];
        $controlFile = PIWIK_INCLUDE_PATH . '/matomo.js';
        $controlUrls = $this->getUrls($controlFile, $baseUrl, $documentRoot);
        $control = (string) @file_get_contents($controlFile, false, null, 0, self::MAX_BODY_BYTES);
        foreach ($controlUrls as $url) {
            $results[$url] = $this->isServed($url, $control) ? self::PROTECTED : self::INCONCLUSIVE;
        }
        if (empty($controlUrls) || in_array(self::INCONCLUSIVE, $results, true)) {
            // the mapping from paths to URLs is not confirmed: a 404 would prove nothing
            return ['status' => self::UNVERIFIED, 'urls' => $results];
        }
        $results = [];

        // an open connection keeps the -wal and -shm files in place
        $connection = new PDO(Connection::getDsn($file), null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $connection->query('SELECT COUNT(*) FROM sqlite_master')->fetchColumn();
        foreach ($fileUrls as $fileUrl) {
            foreach (['', '-wal', '-shm'] as $suffix) {
                $results[$fileUrl . $suffix] = is_file($file . $suffix)
                    ? $this->probeDatabaseFile($fileUrl . $suffix, $suffix, (int) filesize($file . $suffix))
                    : self::INCONCLUSIVE;
            }
        }
        $connection = null;

        foreach ($fileUrls as $fileUrl) {
            $canaryName = 'matomo-probe-' . bin2hex(random_bytes(8)) . '.txt';
            $canary = dirname($file) . DIRECTORY_SEPARATOR . $canaryName;
            $canaryUrl = substr($fileUrl, 0, strrpos($fileUrl, '/') + 1) . $canaryName;
            $marker = 'matomo-probe-' . bin2hex(random_bytes(16));
            if (@file_put_contents($canary, $marker) === strlen($marker)) {
                try {
                    $results[$canaryUrl] = $this->classify($canaryUrl, function (string $body) use ($marker) {
                        return $body !== '' && str_starts_with($marker, $body);
                    });
                } finally {
                    @unlink($canary);
                }
            } else {
                $results[$canaryUrl] = self::INCONCLUSIVE;
            }
        }

        if (in_array(self::EXPOSED, $results, true)) {
            $status = self::EXPOSED;
        } elseif (array_unique(array_values($results)) === [self::PROTECTED]) {
            $status = self::PROTECTED;
        } else {
            $status = self::INCONCLUSIVE;
        }

        return ['status' => $status, 'urls' => $results];
    }

    /**
     * Returns the URL that would serve a file, or null if the file is outside the Matomo directory and the
     * document root.
     */
    public function getUrl(string $file, string $baseUrl, ?string $documentRoot = null): ?string
    {
        return $this->getUrls($file, $baseUrl, $documentRoot)[0] ?? null;
    }

    /**
     * The URLs that could serve a file: through its path as given, and through its real path when a symbolic
     * link makes them differ.
     *
     * @return string[]
     */
    private function getUrls(string $file, string $baseUrl, ?string $documentRoot): array
    {
        $directory = dirname($file);
        $realDirectory = realpath($directory);
        if ($realDirectory === false) {
            return [];
        }

        // pairs, not keys: the Matomo directory can also be the document root, and each gives its own URL
        $roots = [[PIWIK_INCLUDE_PATH, rtrim($baseUrl, '/')]];
        $origin = $this->getOrigin($baseUrl);
        if ($documentRoot !== null && $documentRoot !== '' && $origin !== null) {
            $roots[] = [$documentRoot, $origin];
        }

        $urls = [];
        foreach (array_unique(array_filter([$this->normalizePath($directory), $realDirectory])) as $path) {
            $path .= DIRECTORY_SEPARATOR . basename($file);
            foreach ($roots as [$root, $rootUrl]) {
                // a root reached through a symbolic link is compared both as given and resolved
                foreach (array_unique(array_filter([$this->normalizePath($root), realpath($root)])) as $rootPath) {
                    $relative = $this->getRelativePath($path, $rootPath);
                    if ($relative !== null) {
                        $urls[] = $rootUrl . '/' . $relative;
                    }
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function probeDatabaseFile(string $url, string $suffix, int $size): string
    {
        return $this->classify($url, function (string $body) use ($suffix, $size) {
            if ($size === 0) {
                return $body === '';
            }
            switch ($suffix) {
                case '':
                    return str_starts_with($body, "SQLite format 3\0");
                case '-wal':
                    return str_starts_with($body, "\x37\x7f\x06\x82") || str_starts_with($body, "\x37\x7f\x06\x83");
                default:
                    return str_starts_with($body, pack('V', self::WAL_INDEX_VERSION))
                        || str_starts_with($body, pack('N', self::WAL_INDEX_VERSION));
            }
        });
    }

    /**
     * @param callable(string): bool $isFile whether a 2xx response's body is the file's
     */
    private function classify(string $url, callable $isFile): string
    {
        try {
            $response = call_user_func($this->fetch, $url);
        } catch (\Exception $e) {
            return self::INCONCLUSIVE;
        }

        $status = $response['status'];
        $body = substr($response['body'], 0, self::MAX_BODY_BYTES);
        // whatever the status, a response that carries the file's bytes serves it
        if ($body !== '' && $isFile($body)) {
            return self::EXPOSED;
        }
        if (in_array($status, [401, 403, 404], true)) {
            return self::PROTECTED;
        }
        if ($status >= 200 && $status < 300 && $isFile($body)) {
            return self::EXPOSED;
        }

        return self::INCONCLUSIVE;
    }

    private function isServed(string $url, string $content): bool
    {
        try {
            $response = call_user_func($this->fetch, $url);
        } catch (\Exception $e) {
            return false;
        }

        // the whole start of the file, not a prefix a catch-all page could share
        return $response['status'] === 200 && $content !== '' && substr($response['body'], 0, self::MAX_BODY_BYTES) === $content;
    }

    private function getRelativePath(string $path, string $root): ?string
    {
        $root = rtrim($root, '/\\') . DIRECTORY_SEPARATOR;
        if (!str_starts_with($path, $root)) {
            return null;
        }

        $segments = preg_split('~[/\\\\]~', substr($path, strlen($root)));
        return implode('/', array_map('rawurlencode', $segments));
    }

    /**
     * An absolute path with "." and ".." resolved and symbolic links kept, or null for a relative path.
     */
    private function normalizePath(string $path): ?string
    {
        if (!preg_match('~^(/|[A-Za-z]:[/\\\\])~', $path)) {
            return null;
        }

        $segments = [];
        foreach (preg_split('~[/\\\\]+~', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        $prefix = DIRECTORY_SEPARATOR === '/' ? '/' : '';
        return $prefix . implode(DIRECTORY_SEPARATOR, $segments);
    }

    private function getOrigin(string $url): ?string
    {
        $parts = parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $host = str_contains($parts['host'], ':') ? '[' . trim($parts['host'], '[]') . ']' : $parts['host'];
        return $parts['scheme'] . '://' . $host . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    /**
     * A GET that doesn't follow redirects and reads at most MAX_BODY_BYTES of the body, whatever the server sends.
     *
     * @return array{status: int, body: string}
     */
    private static function fetch(string $url): array
    {
        if (!preg_match('~^https?://~i', $url)) {
            throw new \Exception('Only HTTP(S) URLs are requested');
        }

        if (function_exists('curl_init')) {
            $body = '';
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_CONNECTTIMEOUT => self::TIMEOUT_SECONDS,
                CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
                CURLOPT_USERAGENT => Http::getUserAgent(),
                CURLOPT_CAINFO => GeneralConfig::getConfigValue('custom_cacert_pem') ?: CaBundle::getBundledCaBundlePath(),
                CURLOPT_WRITEFUNCTION => function ($handle, string $chunk) use (&$body) {
                    $body .= $chunk;
                    // returning a length other than the chunk's stops the transfer
                    return strlen($body) >= self::MAX_BODY_BYTES ? 0 : strlen($chunk);
                },
            ]);
            $isComplete = curl_exec($handle) !== false;
            $error = curl_errno($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            curl_close($handle);

            if ($status === 0 || (!$isComplete && $error !== CURLE_WRITE_ERROR)) {
                throw new \Exception('The request failed: ' . $error);
            }

            return ['status' => $status, 'body' => substr($body, 0, self::MAX_BODY_BYTES)];
        }

        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'follow_location' => 0,
            'ignore_errors' => true,
            'timeout' => self::TIMEOUT_SECONDS,
            'user_agent' => Http::getUserAgent(),
        ]]);
        $stream = @fopen($url, 'rb', false, $context);
        if ($stream === false) {
            throw new \Exception('The request failed');
        }
        try {
            $body = (string) stream_get_contents($stream, self::MAX_BODY_BYTES);
            $headers = stream_get_meta_data($stream)['wrapper_data'] ?? [];
        } finally {
            fclose($stream);
        }
        if (!preg_match('~^HTTP/\S+\s+(\d{3})~', (string) ($headers[0] ?? ''), $match)) {
            throw new \Exception('No HTTP status');
        }

        return ['status' => (int) $match[1], 'body' => $body];
    }
}
