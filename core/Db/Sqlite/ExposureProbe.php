<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Sqlite;

use PDO;
use Piwik\Http;

/**
 * Checks over HTTP that the web server doesn't serve a SQLite database file.
 *
 * The database file, its `-wal` and `-shm` files and a canary written next to them are requested. Committed
 * data sits in the `-wal` file, and a deny rule on `*.sqlite` doesn't cover `*.sqlite-wal`, so every file
 * is checked. Each URL is:
 *
 * - protected: HTTP 401, 403 or 404;
 * - exposed: a 2xx response that starts with the file's first bytes, or that is the canary;
 * - inconclusive: anything else, e.g. a failed request, a 5xx, a redirect or a catch-all page.
 *
 * The location is protected only if every URL is, and exposed if any URL is. A failed request never
 * counts as protection.
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
     * Bytes compared with the response. Constant at the start of a database (`SQLite format 3`), of a
     * WAL file (magic number and format version) and of a shared-memory file (WAL-index version).
     */
    private const COMPARED_BYTES = 8;

    private const TIMEOUT_SECONDS = 5;

    /**
     * @var callable(string, array|null): array{status: int, body: string, redirected: bool}
     */
    private $fetch;

    /**
     * @param null|callable(string, array|null): array{status: int, body: string, redirected: bool} $fetch
     *        Requests a URL with an optional byte range; throws on a transport failure.
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
        $fileUrl = $this->getUrl($file, $baseUrl, $documentRoot);
        if ($fileUrl === null) {
            return ['status' => self::OUTSIDE_WEB_ROOT, 'urls' => []];
        }

        // an open connection keeps the -wal and -shm files in place
        $connection = new PDO(Connection::getDsn($file), null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $connection->query('SELECT COUNT(*) FROM sqlite_master')->fetchColumn();

        $results = [];
        foreach (['', '-wal', '-shm'] as $suffix) {
            if (is_file($file . $suffix)) {
                $results[$fileUrl . $suffix] = $this->probeFile($fileUrl . $suffix, $file . $suffix);
            }
        }
        $connection = null;

        $canaryName = 'matomo-probe-' . bin2hex(random_bytes(8)) . '.txt';
        $canary = dirname($file) . DIRECTORY_SEPARATOR . $canaryName;
        $canaryUrl = substr($fileUrl, 0, strrpos($fileUrl, '/') + 1) . $canaryName;
        $marker = 'matomo-probe-' . bin2hex(random_bytes(16));
        if (@file_put_contents($canary, $marker) === strlen($marker)) {
            try {
                $results[$canaryUrl] = $this->probeFile($canaryUrl, $canary);
            } finally {
                @unlink($canary);
            }
        } else {
            $results[$canaryUrl] = self::INCONCLUSIVE;
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
        $path = realpath(dirname($file));
        if ($path === false) {
            return null;
        }
        $path .= DIRECTORY_SEPARATOR . basename($file);

        $relative = $this->getRelativePath($path, PIWIK_INCLUDE_PATH);
        if ($relative !== null) {
            return rtrim($baseUrl, '/') . '/' . $relative;
        }

        if ($documentRoot !== null && $documentRoot !== '') {
            $relative = $this->getRelativePath($path, $documentRoot);
            $origin = $this->getOrigin($baseUrl);
            if ($relative !== null && $origin !== null) {
                return $origin . '/' . $relative;
            }
        }

        return null;
    }

    private function probeFile(string $url, string $file): string
    {
        $expected = (string) @file_get_contents($file, false, null, 0, self::COMPARED_BYTES);

        try {
            // without a range for an empty file: a server answers 416 to it, which would look protected
            $response = call_user_func($this->fetch, $url, $expected === '' ? null : [0, self::COMPARED_BYTES - 1]);
        } catch (\Exception $e) {
            return self::INCONCLUSIVE;
        }

        $status = $response['status'];
        if (!$response['redirected'] && in_array($status, [401, 403, 404], true)) {
            return self::PROTECTED;
        }

        if (!$response['redirected'] && $status >= 200 && $status < 300) {
            $body = $response['body'];
            $isExposed = $expected === '' ? $body === '' : str_starts_with($body, $expected);
            if ($isExposed) {
                return self::EXPOSED;
            }
        }

        return self::INCONCLUSIVE;
    }

    private function getRelativePath(string $path, string $root): ?string
    {
        $root = realpath($root);
        if ($root === false) {
            return null;
        }
        $root = rtrim($root, '/\\') . DIRECTORY_SEPARATOR;
        if (!str_starts_with($path, $root)) {
            return null;
        }

        $segments = preg_split('~[/\\\\]~', substr($path, strlen($root)));
        return implode('/', array_map('rawurlencode', $segments));
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
     * @return array{status: int, body: string, redirected: bool}
     */
    private static function fetch(string $url, ?array $range): array
    {
        // the range keeps the download small: the probe compares the first bytes only
        $response = Http::sendHttpRequest($url, self::TIMEOUT_SECONDS, null, null, 0, false, $range ?? false, true);

        return [
            'status' => (int) ($response['status'] ?? 0),
            'body' => (string) ($response['data'] ?? ''),
            'redirected' => isset($response['effectiveUrl']) && $response['effectiveUrl'] !== $url,
        ];
    }
}
