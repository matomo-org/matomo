<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Api;

use Matomo\Cache\Backend\NullCache;
use Matomo\Cache\Lazy;
use Piwik\Filesystem;
use Piwik\Log\NullLogger;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Environment as TestEnvironment;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service as TestService;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Client as ClientBuilder;

/**
 * @group Plugins
 * @group Marketplace
 * @group ClientTest
 * @group Client
 */
class ClientTest extends IntegrationTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var TestService
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new TestService();
        $this->client = $this->buildClient();
    }

    public function testDownload()
    {
        $this->service->returnFixture('v2.0_plugins_TreemapVisualization_info.json');

        $file = $this->client->download('AnyPluginName');

        $this->assertFileExists($file);
        $this->assertStringEqualsFile($file, 'http://plugins.piwik.org/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=2.16.3&uid=test-unique-id');
        Filesystem::deleteFileIfExists($file);

        $this->assertStringStartsWith(PIWIK_INCLUDE_PATH . '/tmp/latest/plugins/', $file);
        $this->assertStringEndsWith('.zip', $file);
    }

    public function testGetPluginInfoShouldThrowExceptionIfNotAllowedToRequestPlugin()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Exception::class);
        $this->expectExceptionMessage('Requested plugin does not exist.');

        $this->service->returnFixture('v2.0_plugins_CustomPlugin1_info-access_token-notexistingtoken.json');
        $this->client->getPluginInfo('CustomPlugin1');
    }

    public function testFetchSendsUniqueIdAsParamWhenEnvironmentProvidesOne()
    {
        $client = $this->buildClientWithUniqueId('fixed-unique-id-123');

        $this->service->returnFixture('v2.0_plugins_TreemapVisualization_info.json');
        $client->getPluginInfo('TreemapVisualization');

        $this->assertArrayHasKey('uid', $this->service->params);
        $this->assertSame('fixed-unique-id-123', $this->service->params['uid']);
    }

    public function testFetchDoesNotSendUniqueIdParamWhenEmpty()
    {
        $client = $this->buildClientWithUniqueId('');

        $this->service->returnFixture('v2.0_plugins_TreemapVisualization_info.json');
        $client->getPluginInfo('TreemapVisualization');

        $this->assertArrayNotHasKey('uid', $this->service->params);
    }

    public function testGetDownloadUrlAppendsUniqueIdWhenEnvironmentProvidesOne()
    {
        $client = $this->buildClientWithUniqueId('fixed-unique-id-123');

        $this->service->returnFixture('v2.0_plugins_TreemapVisualization_info.json');
        $url = $client->getDownloadUrl('TreemapVisualization');

        $this->assertSame(
            'http://plugins.piwik.org/api/2.0/plugins/TreemapVisualization/download/1.0.1?coreVersion=2.16.3&uid=fixed-unique-id-123',
            $url
        );
    }

    public function testGetDownloadUrlDoesNotAppendUniqueIdWhenEmpty()
    {
        $client = $this->buildClientWithUniqueId('');

        $this->service->returnFixture('v2.0_plugins_TreemapVisualization_info.json');
        $url = $client->getDownloadUrl('TreemapVisualization');

        $this->assertStringNotContainsString('uid=', $url);
    }

    private function buildClient()
    {
        return ClientBuilder::build($this->service);
    }

    private function buildClientWithUniqueId($uniqueId)
    {
        $environment = new class ($uniqueId) extends TestEnvironment {
            private $uniqueId;

            public function __construct($uniqueId)
            {
                $this->uniqueId = $uniqueId;
            }

            public function getUniqueId()
            {
                return $this->uniqueId;
            }
        };

        return new Client($this->service, new Lazy(new NullCache()), new NullLogger(), $environment);
    }
}
