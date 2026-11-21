<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Unit\Db\Sqlite;

use PDO;
use PHPUnit\Framework\TestCase;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ExposureProbe;

/**
 * @group Core
 * @group Db
 */
class ExposureProbeTest extends TestCase
{
    private const BASE_URL = 'https://matomo.example/analytics/';

    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $file;

    /**
     * @var array<string, array|null>
     */
    private $requests = [];

    public function setUp(): void
    {
        parent::setUp();

        if (!Connection::isEnabled()) {
            $this->markTestSkipped('pdo_sqlite is not available');
        }

        $this->directory = $this->makeDirectory(PIWIK_INCLUDE_PATH . '/tmp');
        $this->file = $this->directory . '/matomo.sqlite';
        $this->createDatabase($this->file);
    }

    public function tearDown(): void
    {
        foreach (glob($this->directory . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);

        parent::tearDown();
    }

    public function testEveryFileDeniedIsProtected(): void
    {
        $result = $this->probe(function () {
            return $this->response(403);
        });

        $this->assertSame(ExposureProbe::PROTECTED, $result['status']);
        $this->assertCount(4, $result['urls'], 'database, -wal, -shm and canary');
        $this->assertSame([self::BASE_URL . 'tmp/' . basename($this->directory) . '/matomo.sqlite'], array_slice(array_keys($result['urls']), 0, 1));
    }

    public function testAFilenameDenyRuleThatServesTheWalIsExposed(): void
    {
        // only "*.sqlite" is denied
        $result = $this->probe(function ($url, $range) {
            return str_ends_with($url, '.sqlite') ? $this->response(404) : $this->serve($url, $range);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame(ExposureProbe::PROTECTED, reset($result['urls']));
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][$this->urlOf('matomo.sqlite-wal')]);
    }

    public function testAServedDatabaseIsExposed(): void
    {
        $result = $this->probe(function ($url, $range) {
            return $this->serve($url, $range);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame([ExposureProbe::EXPOSED], array_values(array_unique($result['urls'])));
    }

    public function testAServedCanaryIsExposed(): void
    {
        $result = $this->probe(function ($url, $range) {
            return str_ends_with($url, '.txt') ? $this->serve($url, $range) : $this->response(403);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame([], glob($this->directory . '/*.txt'), 'the canary is deleted');
    }

    /**
     * @dataProvider getInconclusiveResponses
     */
    public function testAnUnprovenResponseIsInconclusive(callable $respond): void
    {
        $result = $this->probe(function ($url, $range) use ($respond) {
            // every other file is protected: one unproven URL is enough
            return str_ends_with($url, '-shm') ? $respond($this) : $this->response(403);
        });

        $this->assertSame(ExposureProbe::INCONCLUSIVE, $result['status']);
        $this->assertSame(ExposureProbe::INCONCLUSIVE, $result['urls'][$this->urlOf('matomo.sqlite-shm')]);
    }

    public function getInconclusiveResponses(): iterable
    {
        yield 'transport failure' => [function () {
            throw new \Exception('Connection refused');
        }];
        yield '500' => [function (self $test) {
            return $test->response(500);
        }];
        yield '503' => [function (self $test) {
            return $test->response(503);
        }];
        yield 'catch-all page' => [function (self $test) {
            return $test->response(200, '<!DOCTYPE html><html>Welcome</html>');
        }];
        yield 'redirect to a 404' => [function (self $test) {
            return ['status' => 404, 'body' => '', 'redirected' => true];
        }];
    }

    public function testAnEmptyFileIsRequestedWithoutARange(): void
    {
        $walSize = null;
        $result = $this->probe(function ($url, $range) use (&$walSize) {
            if (str_ends_with($url, '-wal')) {
                $walSize = filesize($this->file . '-wal');
            }
            // a range on an empty file gets 416, which must not count as protected
            return $range === null ? $this->response(200, '') : $this->response(416);
        });

        $this->assertSame(0, $walSize, 'a fresh WAL file is empty while a connection is open');
        $this->assertNull($this->requests[$this->urlOf('matomo.sqlite-wal')]);
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][$this->urlOf('matomo.sqlite-wal')]);
        $this->assertSame([0, 7], $this->requests[$this->urlOf('matomo.sqlite')]);
        $this->assertSame(ExposureProbe::INCONCLUSIVE, $result['urls'][$this->urlOf('matomo.sqlite')]);
    }

    public function testAFileOutsideTheWebRootIsNotRequested(): void
    {
        $outside = $this->makeDirectory(sys_get_temp_dir());
        try {
            $this->createDatabase($outside . '/matomo.sqlite');

            $result = $this->probe(function () {
                $this->fail('nothing is requested');
            }, $outside . '/matomo.sqlite');

            $this->assertSame(['status' => ExposureProbe::OUTSIDE_WEB_ROOT, 'urls' => []], $result);
        } finally {
            array_map('unlink', glob($outside . '/*'));
            rmdir($outside);
        }
    }

    public function testAFileUnderTheDocumentRootIsRequestedFromTheOrigin(): void
    {
        $root = $this->makeDirectory(sys_get_temp_dir());
        mkdir($root . '/data dir');
        $file = $root . '/data dir/matomo.sqlite';
        $probe = new ExposureProbe();

        try {
            $this->assertSame('https://matomo.example/data%20dir/matomo.sqlite', $probe->getUrl($file, self::BASE_URL, $root));
            $this->assertSame('https://[::1]:8443/data%20dir/matomo.sqlite', $probe->getUrl($file, 'https://[::1]:8443/matomo/', $root));
            $this->assertNull($probe->getUrl($file, self::BASE_URL), 'without a document root');
            $this->assertNull($probe->getUrl($root . '/missing/matomo.sqlite', self::BASE_URL, $root));
        } finally {
            rmdir($root . '/data dir');
            rmdir($root);
        }
    }

    private function probe(callable $respond, ?string $file = null): array
    {
        $probe = new ExposureProbe(function (string $url, ?array $range) use ($respond) {
            $this->requests[$url] = $range;
            return $respond($url, $range);
        });

        return $probe->probe($file ?? $this->file, self::BASE_URL);
    }

    public function response(int $status, string $body = ''): array
    {
        return ['status' => $status, 'body' => $body, 'redirected' => false];
    }

    /**
     * Serves the file the URL names, as a web server without a deny rule does.
     */
    private function serve(string $url, ?array $range): array
    {
        $content = (string) file_get_contents($this->directory . '/' . basename($url));
        if ($range !== null) {
            return $this->response(206, substr($content, $range[0], $range[1] - $range[0] + 1));
        }

        return $this->response(200, $content);
    }

    private function urlOf(string $name): string
    {
        return self::BASE_URL . 'tmp/' . basename($this->directory) . '/' . $name;
    }

    private function makeDirectory(string $parent): string
    {
        $directory = $parent . '/sqlite-probe-test-' . bin2hex(random_bytes(4));
        mkdir($directory, 0700, true);
        return $directory;
    }

    private function createDatabase(string $file): void
    {
        $pdo = new PDO('sqlite:' . $file);
        $pdo->query('PRAGMA journal_mode = WAL');
        $pdo->exec('CREATE TABLE t (a INTEGER)');
        $pdo = null;
    }
}
