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
     * @var string[] the directories and links this test created, removed in reverse order
     */
    private $owned = [];

    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string[]
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
        foreach (array_reverse($this->owned) as $path) {
            if (is_link($path)) {
                unlink($path);
                continue;
            }
            foreach (['matomo.sqlite', 'matomo.sqlite-wal', 'matomo.sqlite-shm'] as $name) {
                if (is_file($path . '/' . $name)) {
                    unlink($path . '/' . $name);
                }
            }
            rmdir($path);
        }

        parent::tearDown();
    }

    public function testEveryFileDeniedIsProtected(): void
    {
        $result = $this->probe(function () {
            return $this->response(403);
        });

        $this->assertSame(ExposureProbe::PROTECTED, $result['status']);
        $this->assertCount(4, $result['urls'], 'database, -wal, -shm and canary');
        $this->assertSame($this->urlOf('matomo.sqlite'), array_key_first($result['urls']));
        $this->assertSame(self::BASE_URL . 'matomo.js', $this->requests[0], 'the mapping is confirmed first');
    }

    public function testDenialsProveNothingWhenMatomoItselfIsNotServedThere(): void
    {
        // e.g. a wrong base URL answered by a catch-all 404
        $result = $this->probe(function () {
            return $this->response(404);
        }, null, null, false);

        $this->assertSame(ExposureProbe::UNVERIFIED, $result['status']);
        $this->assertSame([self::BASE_URL . 'matomo.js'], $this->requests, 'no database file is requested');
    }

    public function testAFilenameDenyRuleThatServesTheShmIsExposed(): void
    {
        // only "*.sqlite" is denied
        $result = $this->probe(function ($url) {
            return str_ends_with($url, '.sqlite') ? $this->response(404) : $this->serve($url);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame(ExposureProbe::PROTECTED, $result['urls'][$this->urlOf('matomo.sqlite')]);
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][$this->urlOf('matomo.sqlite-wal')], 'an empty WAL served empty');
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][$this->urlOf('matomo.sqlite-shm')]);
    }

    public function testAServedDatabaseIsExposed(): void
    {
        $result = $this->probe(function ($url) {
            return $this->serve($url);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame([ExposureProbe::EXPOSED], array_values(array_unique($result['urls'])));
    }

    public function testAServedCanaryIsExposed(): void
    {
        $result = $this->probe(function ($url) {
            return str_ends_with($url, '.txt') ? $this->serve($url) : $this->response(403);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame([], glob($this->directory . '/*.txt'), 'the canary is deleted');
    }

    /**
     * @dataProvider getInconclusiveResponses
     */
    public function testAnUnprovenResponseIsInconclusive(callable $respond): void
    {
        $result = $this->probe(function ($url) use ($respond) {
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
        yield 'redirect' => [function (self $test) {
            return $test->response(302);
        }];
        yield 'range not satisfiable' => [function (self $test) {
            return $test->response(416);
        }];
        yield 'catch-all page' => [function (self $test) {
            return $test->response(200, '<!DOCTYPE html><html>Welcome</html>');
        }];
    }

    public function testADatabaseWithoutItsWalFilesIsInconclusive(): void
    {
        $directory = $this->makeDirectory(PIWIK_INCLUDE_PATH . '/tmp');
        $pdo = new PDO('sqlite:' . $directory . '/matomo.sqlite');
        $pdo->exec('CREATE TABLE t (a INTEGER)');
        $pdo = null;

        $result = $this->probe(function () {
            return $this->response(403);
        }, $directory . '/matomo.sqlite');

        $this->assertSame(ExposureProbe::INCONCLUSIVE, $result['status']);
    }

    public function testADirectoryReachedThroughALinkIsRequestedThroughBothPaths(): void
    {
        $link = PIWIK_INCLUDE_PATH . '/tmp/sqlite-probe-link-' . bin2hex(random_bytes(4));
        if (!@symlink($this->directory, $link)) {
            $this->markTestSkipped('symbolic links are not available');
        }
        $this->owned[] = $link;
        $linkUrl = self::BASE_URL . 'tmp/' . basename($link) . '/matomo.sqlite';

        // the real directory is denied, the link is served
        $result = $this->probe(function ($url) use ($link) {
            return str_contains($url, basename($link)) ? $this->serve($url) : $this->response(403);
        }, $link . '/matomo.sqlite');

        $this->assertSame(ExposureProbe::EXPOSED, $result['status']);
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][$linkUrl]);
        $this->assertSame(ExposureProbe::PROTECTED, $result['urls'][$this->urlOf('matomo.sqlite')]);
    }

    public function testAFileOutsideAKnownDocumentRootIsNotRequested(): void
    {
        $outside = $this->makeDirectory(sys_get_temp_dir());
        $root = $this->makeDirectory(sys_get_temp_dir());
        $this->createDatabase($outside . '/matomo.sqlite');

        $result = $this->probe(function () {
            $this->fail('nothing is requested');
        }, $outside . '/matomo.sqlite', $root);

        $this->assertSame(['status' => ExposureProbe::OUTSIDE_WEB_ROOT, 'urls' => []], $result);
    }

    public function testAFileOutsideMatomoIsUnverifiedWithoutADocumentRoot(): void
    {
        $outside = $this->makeDirectory(sys_get_temp_dir());
        $this->createDatabase($outside . '/matomo.sqlite');

        $result = $this->probe(function () {
            $this->fail('nothing is requested');
        }, $outside . '/matomo.sqlite');

        $this->assertSame(['status' => ExposureProbe::UNVERIFIED, 'urls' => []], $result);
    }

    public function testADenialCarryingTheFileIsExposed(): void
    {
        $result = $this->probe(function ($url) {
            return str_ends_with($url, '.sqlite') ? ['status' => 404, 'body' => $this->serve($url)['body']] : $this->response(403);
        });

        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][$this->urlOf('matomo.sqlite')]);
    }

    public function testAControlSharingOnlyAPrefixWithMatomoIsNotEnough(): void
    {
        // "/" starts matomo.js too, as it may start a catch-all page
        $probe = new ExposureProbe(function (string $url) {
            $this->requests[] = $url;
            return str_ends_with($url, '/matomo.js') ? $this->response(200, '/') : $this->response(404);
        });

        $this->assertSame(ExposureProbe::UNVERIFIED, $probe->probe($this->file, self::BASE_URL)['status']);
    }

    public function testMatomoAsTheDocumentRootIsRequestedThroughBothMappings(): void
    {
        // e.g. behind a proxy that adds /analytics/: which mapping the server uses isn't known
        $result = $this->probe(function () {
            return $this->response(403);
        }, null, PIWIK_INCLUDE_PATH);

        $this->assertContains('https://matomo.example/matomo.js', $this->requests);
        $this->assertContains(self::BASE_URL . 'matomo.js', $this->requests);
        $this->assertSame(ExposureProbe::UNVERIFIED, $result['status'], 'only one of them serves Matomo');
    }

    public function testADirectoryLinkedUnderALinkedDocumentRootIsRequested(): void
    {
        $realRoot = $this->makeDirectory(sys_get_temp_dir());
        $root = $realRoot . '-link';
        $data = $this->makeDirectory(sys_get_temp_dir());
        if (!@symlink($realRoot, $root) || !@symlink($data, $realRoot . '/data')) {
            $this->markTestSkipped('symbolic links are not available');
        }
        array_push($this->owned, $root, $realRoot . '/data');
        $this->createDatabase($data . '/matomo.sqlite');

        $result = $this->probe(function () {
            return $this->response(403);
        }, $root . '/data/matomo.sqlite', $root);

        $this->assertSame(ExposureProbe::PROTECTED, $result['status']);
        $this->assertArrayHasKey('https://matomo.example/data/matomo.sqlite', $result['urls']);
    }

    public function testAFileUnderTheDocumentRootIsRequestedFromTheOrigin(): void
    {
        $root = $this->makeDirectory(sys_get_temp_dir());
        mkdir($root . '/data dir');
        $this->owned[] = $root . '/data dir';
        $file = $root . '/data dir/matomo.sqlite';
        $probe = new ExposureProbe();

        $this->assertSame('https://matomo.example/data%20dir/matomo.sqlite', $probe->getUrl($file, self::BASE_URL, $root));
        $this->assertSame('https://[::1]:8443/data%20dir/matomo.sqlite', $probe->getUrl($file, 'https://[::1]:8443/matomo/', $root));
        $this->assertNull($probe->getUrl($file, self::BASE_URL), 'without a document root');
        $this->assertNull($probe->getUrl($root . '/missing/matomo.sqlite', self::BASE_URL, $root));
    }

    private function probe(callable $respond, ?string $file = null, ?string $documentRoot = null, bool $servesMatomo = true): array
    {
        $probe = new ExposureProbe(function (string $url) use ($respond, $servesMatomo) {
            $this->requests[] = $url;
            if ($servesMatomo && $url === self::BASE_URL . 'matomo.js') {
                return $this->response(200, (string) file_get_contents(PIWIK_INCLUDE_PATH . '/matomo.js'));
            }
            return $respond($url);
        });

        return $probe->probe($file ?? $this->file, self::BASE_URL, $documentRoot);
    }

    public function response(int $status, string $body = ''): array
    {
        return ['status' => $status, 'body' => $body];
    }

    /**
     * Serves the file the URL names, as a web server without a deny rule does.
     *
     * Reading the live files directly is acceptable here only because this process holds no SQLite lock worth
     * keeping: the probe's connection runs a single read.
     */
    private function serve(string $url): array
    {
        return $this->response(200, (string) file_get_contents($this->directory . '/' . basename(rawurldecode($url))));
    }

    private function urlOf(string $name): string
    {
        return self::BASE_URL . 'tmp/' . basename($this->directory) . '/' . $name;
    }

    private function makeDirectory(string $parent): string
    {
        $directory = $parent . '/sqlite-probe-test-' . bin2hex(random_bytes(4));
        mkdir($directory, 0700, true);
        $this->owned[] = $directory;
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
