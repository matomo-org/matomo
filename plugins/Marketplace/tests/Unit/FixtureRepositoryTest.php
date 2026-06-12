<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Unit;

use Piwik\Plugins\Marketplace\tests\Framework\Mock\FixtureRepository;

/**
 * @group Marketplace
 * @group FixtureRepository
 */
class FixtureRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var FixtureRepository
     */
    private $repository;

    public function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/marketplace_fixture_repo_test_' . uniqid();
        mkdir($this->tmpDir, 0777, true);

        $this->repository = new FixtureRepository($this->tmpDir);

        FixtureRepository::clearOverrides();
        FixtureRepository::clearRegisteredManifestDirectories();
        FixtureRepository::clearLoggedMisses();
    }

    public function tearDown(): void
    {
        FixtureRepository::clearOverrides();
        FixtureRepository::clearRegisteredManifestDirectories();
        FixtureRepository::clearLoggedMisses();

        $this->cleanDir($this->tmpDir);
    }

    public function testBuildCanonicalKeyDropsEnvironmentNoise(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins?keywords=login&piwik=5.1.0&php=8.2.10&mysql=8.0.32&prefer_stable=1&release_channel=latest_stable&num_websites=3';

        $this->assertSame(
            '/api/2.0/plugins?keywords=login',
            $this->repository->buildCanonicalKey($url, null)
        );
    }

    public function testBuildCanonicalKeyStripsPiwikWhenCurrentMajor(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins?piwik=5.12.0-alpha&sort=alpha';

        $this->assertSame(
            '/api/2.0/plugins?sort=alpha',
            $this->repository->buildCanonicalKey($url, null)
        );
    }

    public function testBuildCanonicalKeyKeepsPiwikForOlderMajor(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins/TreemapVisualization/info?piwik=4.16.2';

        $this->assertSame(
            '/api/2.0/plugins/TreemapVisualization/info?piwik=4.16.2',
            $this->repository->buildCanonicalKey($url, null)
        );
    }

    public function testBuildCanonicalKeyDropsNumUsersAsNoise(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins?purchase_type=paid&num_users=201&piwik=5.1.0';

        $this->assertSame(
            '/api/2.0/plugins?purchase_type=paid',
            $this->repository->buildCanonicalKey($url, null)
        );
    }

    public function testBuildCanonicalKeyDropsEmptyParams(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins?keywords=&query=&sort=&purchase_type=';

        $this->assertSame(
            '/api/2.0/plugins',
            $this->repository->buildCanonicalKey($url, null)
        );
    }

    public function testBuildCanonicalKeyFoldsAccessTokenFromPostData(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/consumer';
        $key = $this->repository->buildCanonicalKey($url, ['access_token' => 'abc123']);

        $this->assertSame('/api/2.0/consumer?access_token=abc123', $key);
    }

    public function testBuildCanonicalKeySortsQueryParams(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins?z=1&a=2&m=3';

        $this->assertSame(
            '/api/2.0/plugins?a=2&m=3&z=1',
            $this->repository->buildCanonicalKey($url, null)
        );
    }

    public function testRespondReturnsRawStringForJsonFixture(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => 'info.json',
        ]);
        file_put_contents($this->tmpDir . '/info.json', '{"version":"2.0"}');

        [$response, $status, $headers] = $this->respond('https://plugins.matomo.org/api/2.0/info');

        $this->assertSame('{"version":"2.0"}', $response);
        $this->assertSame(200, $status);
        $this->assertSame([], $headers);
    }

    public function testRespondSetsManifestStatusForNon200Entry(): void
    {
        $this->writeManifest([
            '/api/2.0/consumer' => ['file' => 'consumer.json', 'status' => 401],
        ]);
        file_put_contents($this->tmpDir . '/consumer.json', '{"error":"Not authenticated"}');

        [$response, $status, $headers] = $this->respond('https://plugins.matomo.org/api/2.0/consumer');

        $this->assertSame('{"error":"Not authenticated"}', $response);
        $this->assertSame(401, $status);
        $this->assertSame([], $headers);
    }

    public function testRespondWritesToDestinationPathAndSetsHandledSentinel(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'plugins.json',
        ]);
        file_put_contents($this->tmpDir . '/plugins.json', '{"plugins":[]}');

        $destination = $this->tmpDir . '/download.json';

        [$response, $status] = $this->respond(
            'https://plugins.matomo.org/api/2.0/plugins',
            ['destinationPath' => $destination]
        );

        // Sentinel: a non-null reference marks the event as handled in
        // core/Http.php so the real transport is skipped.
        $this->assertSame('', $response);
        $this->assertSame(200, $status);
        $this->assertSame('{"plugins":[]}', file_get_contents($destination));
    }

    public function testRespondOnMarketplaceMissLeavesReferencesUntouched(): void
    {
        $this->writeManifest([]);

        $response = null;
        $status = null;
        $headers = [];

        $this->repository->respond(
            'https://plugins.matomo.org/api/2.0/plugins',
            [],
            $response,
            $status,
            $headers
        );

        // No fixture, no thrown exception, no deprecation. Http.php must see
        // unset references so it falls through to the real transport — that's
        // what keeps downstream plugin CI green when they hit un-cached URLs.
        $this->assertNull($response);
        $this->assertNull($status);
        $this->assertSame([], $headers);
    }

    public function testRespondMissDoesNotRaiseAnyPhpError(): void
    {
        $this->writeManifest([]);

        $captured = [];
        set_error_handler(function ($errno, $errstr) use (&$captured) {
            $captured[] = [$errno, $errstr];
            return true;
        });

        try {
            $response = null;
            $status = null;
            $headers = [];
            $this->repository->respond('https://plugins.matomo.org/api/2.0/plugins', [], $response, $status, $headers);
        } finally {
            restore_error_handler();
        }

        // We must never raise an E_USER_DEPRECATED (or any other PHP error)
        // on a miss — PHPUnit's convertDeprecationsToExceptions default would
        // turn that into a thrown exception in plugin CI runs that hit
        // un-cached Marketplace URLs.
        $this->assertSame([], $captured);
    }

    public function testOverrideTakesPrecedenceOverManifest(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => 'info.json',
        ]);
        file_put_contents($this->tmpDir . '/info.json', '"manifest body"');
        file_put_contents($this->tmpDir . '/override.json', '"override body"');

        FixtureRepository::setOverride('/api/2.0/info', 'override.json');

        [$response] = $this->respond('https://plugins.matomo.org/api/2.0/info');

        $this->assertSame('"override body"', $response);
    }

    public function testClearOverridesRemovesPriorOverride(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => 'info.json',
        ]);
        file_put_contents($this->tmpDir . '/info.json', '"manifest body"');
        file_put_contents($this->tmpDir . '/override.json', '"override body"');

        FixtureRepository::setOverride('/api/2.0/info', 'override.json');
        FixtureRepository::clearOverrides();

        [$response] = $this->respond('https://plugins.matomo.org/api/2.0/info');

        $this->assertSame('"manifest body"', $response);
    }

    public function testRespondRejectsPathTraversalInManifestFilename(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => '../escaped.json',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/unsafe filename/');

        $response = null;
        $status = null;
        $headers = [];
        $this->repository->respond(
            'https://plugins.matomo.org/api/2.0/info',
            [],
            $response,
            $status,
            $headers
        );
    }

    public function testManifestRejectsInvalidStatusValue(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => ['file' => 'info.json', 'status' => 'oops'],
        ]);
        file_put_contents($this->tmpDir . '/info.json', '{}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/invalid HTTP status/');

        $this->respond('https://plugins.matomo.org/api/2.0/info');
    }

    public function testBuildCanonicalKeyHandlesMalformedUrlWithoutWarning(): void
    {
        // parse_url returns false for severely malformed URLs (e.g. with a
        // bare colon scheme). The key builder must coerce to [] so callers
        // don't trip PHP 8 "array offset on bool" warnings.
        $key = $this->repository->buildCanonicalKey('http://:80', null);
        $this->assertSame('', $key);
    }

    public function testManifestRejectsTypoKeysMissingLeadingSlash(): void
    {
        file_put_contents(
            $this->tmpDir . '/manifest.json',
            json_encode(['api/2.0/info' => 'info.json'])
        );
        $this->repository->reloadManifest();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/unrecognised key/');

        $this->respond('https://plugins.matomo.org/api/2.0/info');
    }

    public function testManifestNonUrlKeysAreIgnored(): void
    {
        file_put_contents(
            $this->tmpDir . '/manifest.json',
            json_encode([
                '__comment__' => 'docs',
                '/api/2.0/info' => 'info.json',
            ])
        );
        $this->repository->reloadManifest();
        file_put_contents($this->tmpDir . '/info.json', '{"ok":true}');

        [$response] = $this->respond('https://plugins.matomo.org/api/2.0/info');

        $this->assertSame('{"ok":true}', $response);
    }

    public function testRespondThrowsOnMalformedJsonFixture(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => 'broken.json',
        ]);
        file_put_contents($this->tmpDir . '/broken.json', '{ this is not json');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/is not valid JSON/');

        $this->respond('https://plugins.matomo.org/api/2.0/info');
    }

    public function testRespondLeavesReferencesUntouchedForUnknownHosts(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'plugins.json',
        ]);
        file_put_contents($this->tmpDir . '/plugins.json', '{}');

        $response = null;
        $status = null;
        $headers = [];

        $this->repository->respond(
            'http://notexisting49.plugins.piwk.org/api/2.0/plugins',
            [],
            $response,
            $status,
            $headers
        );

        $this->assertNull($response);
        $this->assertNull($status);
        $this->assertSame([], $headers);
    }

    public function testRespondServesDownloadFromManifestWhenMatched(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=4.16.2' => 'fake.zip',
        ]);
        file_put_contents($this->tmpDir . '/fake.zip', 'ZIP-BYTES');

        $destination = $this->tmpDir . '/out.zip';
        [$response, $status] = $this->respond(
            'http://plugins.piwik.org/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=4.16.2',
            ['destinationPath' => $destination]
        );

        $this->assertSame('', $response);
        $this->assertSame(200, $status);
        $this->assertSame('ZIP-BYTES', file_get_contents($destination));
    }

    public function testRespondServesInfoUrlsWhenMatchedInManifest(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins/SecurityInfo/info' => 'security.json',
        ]);
        file_put_contents($this->tmpDir . '/security.json', '{"name":"SecurityInfo"}');

        [$response] = $this->respond('https://plugins.matomo.org/api/2.0/plugins/SecurityInfo/info?piwik=5.12.0');

        $this->assertSame('{"name":"SecurityInfo"}', $response);
    }

    public function testMissingFixtureFileThrowsClearError(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'gone.json',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/fixture file.*is missing/');

        $this->respond('https://plugins.matomo.org/api/2.0/plugins');
    }

    public function testRespondFoldsAccessTokenFromArrayBody(): void
    {
        $this->writeManifest([
            '/api/2.0/consumer?access_token=abc' => 'consumer.json',
        ]);
        file_put_contents($this->tmpDir . '/consumer.json', '{"who":"abc"}');

        [$response] = $this->respond(
            'https://plugins.matomo.org/api/2.0/consumer',
            ['body' => ['access_token' => 'abc']]
        );

        $this->assertSame('{"who":"abc"}', $response);
    }

    public function testRespondFoldsAccessTokenFromUrlEncodedStringBody(): void
    {
        $this->writeManifest([
            '/api/2.0/consumer?access_token=abc' => 'consumer.json',
        ]);
        file_put_contents($this->tmpDir . '/consumer.json', '{"who":"abc"}');

        [$response] = $this->respond(
            'https://plugins.matomo.org/api/2.0/consumer',
            ['body' => 'access_token=abc&other=ignored']
        );

        $this->assertSame('{"who":"abc"}', $response);
    }

    public function testRegisteredPluginManifestOverridesCoreEntryAndIsResolvedAgainstPluginDir(): void
    {
        $pluginDir = $this->tmpDir . '/plugin_fixtures';
        mkdir($pluginDir, 0777, true);

        $this->writeManifest([
            '/api/2.0/info' => 'info.json',
        ]);
        file_put_contents($this->tmpDir . '/info.json', '"core body"');

        file_put_contents(
            $pluginDir . '/manifest.json',
            json_encode(['/api/2.0/info' => 'plugin-info.json'])
        );
        file_put_contents($pluginDir . '/plugin-info.json', '"plugin body"');

        FixtureRepository::registerManifestDirectory($pluginDir);
        $this->repository->reloadManifest();

        [$response] = $this->respond('https://plugins.matomo.org/api/2.0/info');

        $this->assertSame('"plugin body"', $response);
    }

    public function testRegisteredPluginManifestFillsGapsForCoreKeys(): void
    {
        $pluginDir = $this->tmpDir . '/plugin_fixtures';
        mkdir($pluginDir, 0777, true);

        $this->writeManifest([]);

        file_put_contents(
            $pluginDir . '/manifest.json',
            json_encode(['/api/2.0/plugins/MyPlugin/info' => 'my-plugin.json'])
        );
        file_put_contents($pluginDir . '/my-plugin.json', '"my plugin body"');

        FixtureRepository::registerManifestDirectory($pluginDir);
        $this->repository->reloadManifest();

        [$response] = $this->respond('https://plugins.matomo.org/api/2.0/plugins/MyPlugin/info');

        $this->assertSame('"my plugin body"', $response);
    }

    public function testClearRegisteredManifestDirectoriesRemovesPluginEntries(): void
    {
        $pluginDir = $this->tmpDir . '/plugin_fixtures';
        mkdir($pluginDir, 0777, true);

        $this->writeManifest([]);

        file_put_contents(
            $pluginDir . '/manifest.json',
            json_encode(['/api/2.0/plugins/MyPlugin/info' => 'my-plugin.json'])
        );
        file_put_contents($pluginDir . '/my-plugin.json', '"x"');

        FixtureRepository::registerManifestDirectory($pluginDir);
        FixtureRepository::clearRegisteredManifestDirectories();
        $this->repository->reloadManifest();

        // After clearing, the previously registered key reverts to a miss:
        // references stay unset so Http.php falls through to the real transport.
        [$response, $status] = $this->respond('https://plugins.matomo.org/api/2.0/plugins/MyPlugin/info');

        $this->assertNull($response);
        $this->assertNull($status);
    }

    /**
     * @param array<string, string|array> $entries
     */
    private function writeManifest(array $entries): void
    {
        file_put_contents(
            $this->tmpDir . '/manifest.json',
            json_encode($entries)
        );
        $this->repository->reloadManifest();
    }

    /**
     * Convenience wrapper that drives the by-reference event API and returns
     * the resulting references for assertion.
     *
     * @param array<string, mixed> $eventParams
     * @return array{0: string|null, 1: int|null, 2: array}
     */
    private function respond(string $url, array $eventParams = []): array
    {
        $response = null;
        $status = null;
        $headers = [];

        $this->repository->respond($url, $eventParams, $response, $status, $headers);

        return [$response, $status, $headers];
    }

    private function cleanDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) as $file
        ) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($dir);
    }
}
