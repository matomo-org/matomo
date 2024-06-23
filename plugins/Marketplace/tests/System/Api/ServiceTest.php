<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\System\Api;

use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Plugins
 * @group Marketplace
 * @group ServiceTest
 * @group Service
 */
class ServiceTest extends SystemTestCase
{
    private $domain = 'http://plugins.piwik.org';

    public function testShouldUseVersion2()
    {
        $service = $this->buildService();
        $this->assertSame('2.0', $service->getVersion());
    }

    public function testGetDomainShouldReturnPassedDomain()
    {
        $service = $this->buildService();
        $this->assertSame($this->domain, $service->getDomain());
    }

    public function testAuthenticateGetAccessTokenShouldSaveTokenIfOnlyHasAlNumValues()
    {
        $service = $this->buildService();
        $service->authenticate('123456789abcdefghij');
        $this->assertSame('123456789abcdefghij', $service->getAccessToken());
    }

    public function testHasAccessToken()
    {
        $service = $this->buildService();
        $this->assertFalse($service->hasAccessToken());
        $service->authenticate('123456789abcdefghij');
        $this->assertTrue($service->hasAccessToken());
    }

    public function testAuthenticateGetAccessTokenEmptyTokenShouldUnsetToken()
    {
        $service = $this->buildService();
        $service->authenticate('');
        $this->assertNull($service->getAccessToken());
    }

    public function testAuthenticateGetAccessTokenInvalidTokenContainingInvalidCharactersShouldBeIgnored()
    {
        $service = $this->buildService();
        $service->authenticate('123_-4?');
        $this->assertNull($service->getAccessToken());
    }

    public function testFetchShouldCallMarketplaceApiWithActionAndReturnArrays()
    {
        $service = $this->buildService();
        $response = $service->fetch('plugins', array());

        $this->assertTrue(is_array($response));
        $this->assertArrayHasKey('plugins', $response);
        $this->assertGreaterThanOrEqual(30, count($response['plugins']));
        foreach ($response['plugins'] as $plugin) {
            $this->assertArrayHasKey('name', $plugin);
        }
    }

    public function testFetchShouldCallMarketplaceApiWithGivenParamsAndReturnArrays()
    {
        $keyword = 'login';
        $service = $this->buildService();
        $response = $service->fetch('plugins', array('keywords' => $keyword));

        $this->assertLessThan(20, count($response['plugins']));
        foreach ($response['plugins'] as $plugin) {
            self::assertTrue(in_array($keyword, $plugin['keywords']));
        }
    }

    public function testFetchShouldThrowExceptionWhenNotBeingAuthenticated()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Service\Exception::class);
        $this->expectExceptionCode(101);
        $this->expectExceptionMessage('Not authenticated');

        $service = $this->buildService();
        $service->fetch('consumer', array());
    }

    public function testFetchShouldThrowExceptionWhenBeingAuthenticatedWithInvalidTokens()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Service\Exception::class);
        $this->expectExceptionCode(101);
        $this->expectExceptionMessage('Not authenticated');

        $service = $this->buildService();
        $service->authenticate('1234567890');
        $service->fetch('consumer', array());
    }

    public function testDownloadShouldReturnRawResultForAbsoluteUrl()
    {
        $service = $this->buildService();
        $response = $service->download($this->domain . '/api/2.0/plugins');

        self::assertIsString($response);
        $this->assertNotEmpty($response);
        $this->assertStringStartsWith('{"plugins"', $response);
    }

    public function testDownloadShouldSaveResultInFileIfPathGiven()
    {
        $path = StaticContainer::get('path.tmp') . '/marketplace_test_file.json';

        Filesystem::deleteFileIfExists($path);

        $service = $this->buildService();
        $response = $service->download($this->domain . '/api/2.0/plugins', $path);

        $this->assertTrue($response);
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('{"plugins"', $content);

        Filesystem::deleteFileIfExists($path);
    }

    public function testTimeoutInvalidServiceShouldFailIfNotReachable()
    {
        // The exact exception may vary depending on the connection backend being used (curl, sockets, fopen, etc), so
        // we just check that some exception is thrown when the method is passed an invalid domain
        $this->expectException(\Exception::class);
        $service = $this->buildService();
        $service->download('http://notexisting49.plugins.piwk.org/api/2.0/plugins', null);
    }

    private function buildService()
    {
        return new Service($this->domain);
    }
}
