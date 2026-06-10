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
        FixtureRepository::clearPostProcessors();
    }

    public function tearDown(): void
    {
        FixtureRepository::clearOverrides();
        FixtureRepository::clearPostProcessors();

        if (is_dir($this->tmpDir)) {
            foreach (glob($this->tmpDir . '/*') as $file) {
                @unlink($file);
            }
            @rmdir($this->tmpDir);
        }
    }

    public function testBuildCanonicalKeyDropsEnvironmentNoise(): void
    {
        $url = 'https://plugins.matomo.org/api/2.0/plugins?keywords=login&piwik=5.1.0&php=8.2.10&mysql=8.0.32&prefer_stable=1&release_channel=latest_stable&num_websites=3';

        $this->assertSame(
            '/api/2.0/plugins?keywords=login',
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

    public function testInterceptReturnsRawStringForJsonFixture(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => 'info.json',
        ]);
        file_put_contents($this->tmpDir . '/info.json', '{"version":"2.0"}');

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/info',
            null,
            null,
            false
        );

        $this->assertSame('{"version":"2.0"}', $result);
    }

    public function testInterceptReturnsExtendedShapeWhenRequested(): void
    {
        $this->writeManifest([
            '/api/2.0/consumer' => ['file' => 'consumer.json', 'status' => 401],
        ]);
        file_put_contents($this->tmpDir . '/consumer.json', '{"error":"Not authenticated"}');

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/consumer',
            null,
            null,
            true
        );

        $this->assertSame(
            ['status' => 401, 'headers' => [], 'data' => '{"error":"Not authenticated"}'],
            $result
        );
    }

    public function testInterceptWritesToDestinationPathAndReturnsTrue(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'plugins.json',
        ]);
        file_put_contents($this->tmpDir . '/plugins.json', '{"plugins":[]}');

        $destination = $this->tmpDir . '/download.json';

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/plugins',
            $destination,
            null,
            false
        );

        $this->assertTrue($result);
        $this->assertSame('{"plugins":[]}', file_get_contents($destination));
    }

    public function testInterceptReturnsNullOnMissSoCallerFallsThroughToRealHttp(): void
    {
        $this->writeManifest([]);

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/plugins',
            null,
            null,
            false
        );

        $this->assertNull($result);
    }

    public function testOverrideTakesPrecedenceOverManifest(): void
    {
        $this->writeManifest([
            '/api/2.0/info' => 'info.json',
        ]);
        file_put_contents($this->tmpDir . '/info.json', 'manifest body');
        file_put_contents($this->tmpDir . '/override.json', 'override body');

        FixtureRepository::setOverride('/api/2.0/info', 'override.json');

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/info',
            null,
            null,
            false
        );

        $this->assertSame('override body', $result);
    }

    public function testPostProcessorMutatesJsonBody(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'plugins.json',
        ]);
        file_put_contents(
            $this->tmpDir . '/plugins.json',
            '{"coverImage":"https://plugins.matomo.org/img/categories/insights.png"}'
        );

        FixtureRepository::registerPostProcessor('rewriteImages', function ($json) {
            return str_replace('https://plugins.matomo.org', 'plugins/Marketplace/tests/resources/images', $json);
        });

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/plugins',
            null,
            null,
            false
        );

        $this->assertStringContainsString('plugins/Marketplace/tests/resources/images/img/categories/insights.png', $result);
    }

    public function testInterceptSkipsUnknownHostsAndReturnsNull(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'plugins.json',
        ]);
        file_put_contents($this->tmpDir . '/plugins.json', '{}');

        $result = $this->repository->intercept(
            'http://notexisting49.plugins.piwk.org/api/2.0/plugins',
            null,
            null,
            false
        );

        $this->assertNull($result);
    }

    public function testInterceptPassesThroughBinaryDownloadsWithoutFixture(): void
    {
        $this->writeManifest([]);

        $result = $this->repository->intercept(
            'http://plugins.piwik.org/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=4.16.2',
            '/tmp/whatever.zip',
            null,
            false
        );

        $this->assertNull($result);
    }

    public function testInterceptStillServesDownloadFromManifestWhenMatched(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=4.16.2' => 'fake.zip',
        ]);
        file_put_contents($this->tmpDir . '/fake.zip', 'ZIP-BYTES');

        $destination = $this->tmpDir . '/out.zip';
        $result = $this->repository->intercept(
            'http://plugins.piwik.org/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=4.16.2',
            $destination,
            null,
            false
        );

        $this->assertTrue($result);
        $this->assertSame('ZIP-BYTES', file_get_contents($destination));
    }

    public function testInterceptPassesThroughInfoUrlsWithoutFixture(): void
    {
        $this->writeManifest([]);

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/plugins/AbTesting/info?piwik=5.12.0',
            null,
            null,
            false
        );

        $this->assertNull($result);
    }

    public function testInterceptPassesThroughInfoUrlsForOlderPiwikMajor(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins/TreemapVisualization/info' => 'whatever.json',
        ]);
        file_put_contents($this->tmpDir . '/whatever.json', '{}');

        $result = $this->repository->intercept(
            'https://plugins.piwik.org/api/2.0/plugins/TreemapVisualization/info?piwik=4.16.2',
            null,
            null,
            false
        );

        $this->assertNull($result);
    }

    public function testInterceptStillServesInfoUrlsWhenMatchedInManifest(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins/SecurityInfo/info' => 'security.json',
        ]);
        file_put_contents($this->tmpDir . '/security.json', '{"name":"SecurityInfo"}');

        $result = $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/plugins/SecurityInfo/info?piwik=5.12.0',
            null,
            null,
            false
        );

        $this->assertSame('{"name":"SecurityInfo"}', $result);
    }

    public function testMissingFixtureFileThrowsClearError(): void
    {
        $this->writeManifest([
            '/api/2.0/plugins' => 'gone.json',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/fixture file.*is missing/');

        $this->repository->intercept(
            'https://plugins.matomo.org/api/2.0/plugins',
            null,
            null,
            false
        );
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
}
