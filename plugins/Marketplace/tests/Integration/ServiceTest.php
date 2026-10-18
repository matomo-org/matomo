<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Api;

use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service as TestService;

/**
 * @group Plugins
 * @group Marketplace
 * @group Service
 * @group ServiceTest
 */
class ServiceTest extends IntegrationTestCase
{
    /**
     * @var TestService
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new TestService();
    }

    public function testFetchThrowsApiErrorWhenMarketplaceReturnsAnError()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Service\Exception::class);
        $this->expectExceptionCode(101);
        $this->expectExceptionMessage('Requested plugin does not exist.');

        $this->service->returnFixture('v2.0_plugins_CustomPlugin1_info-access_token-notexistingtoken.json');
        $this->service->fetch('plugins/CustomPlugin1/info', array());
    }

    public function testFetchThrowsHttpErrorWhenMarketplaceReturnsNoResultWhichMeansHttpError()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Service\Exception::class);
        $this->expectExceptionCode(100);
        $this->expectExceptionMessage('There was an error reading the response from the Marketplace');

        $this->service->setOnDownloadCallback(function () {
            return null;
        });
        $this->service->fetch('plugins/CustomPlugin1/info', array());
    }

    public function testFetchJsonDecodesTheHttpResponse()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-consumer1_paid2_custom1.json');
        $consumer = $this->service->fetch('consumer', array());
        $this->assertTrue(is_array($consumer));
        $this->assertNotEmpty($consumer);
    }

    public function testFetchUsesGetWhenThereIsNoPostData()
    {
        $request = $this->captureRequest(function (Service $service) {
            $service->fetch('plugins', ['keywords' => 'login']);
        });

        $this->assertSame('GET', $request['method']);
        $this->assertEmpty($request['body']);
        $this->assertStringContainsString('keywords=login', $request['url']);
    }

    public function testFetchKeepsTheAccessTokenInAPostBodyWhenAuthenticated()
    {
        $request = $this->captureRequest(function (Service $service) {
            $service->authenticate('abc123');
            $service->fetch('consumer', []);
        });

        $this->assertSame('POST', $request['method']);
        $this->assertSame(['access_token' => 'abc123'], $request['body']);
        $this->assertStringNotContainsString('abc123', $request['url']);
    }

    public function testFetchUsesPostWhenPostDataIsGivenWithoutAnAccessToken()
    {
        $request = $this->captureRequest(function (Service $service) {
            $service->fetch('createAccount', [], ['email' => 'user@example.org']);
        });

        $this->assertSame('POST', $request['method']);
        $this->assertSame(['email' => 'user@example.org'], $request['body']);
        $this->assertStringNotContainsString('example.org', $request['url']);
    }

    public function testFetchUsesPostWhenAnEmptyPostDataArrayIsGiven()
    {
        $request = $this->captureRequest(function (Service $service) {
            $service->fetch('plugins/FreePlugin/freeTrial', [], []);
        });

        $this->assertSame('POST', $request['method']);
    }

    public function testDownloadToAFileUsesGetWithoutAnAccessToken()
    {
        $path = StaticContainer::get('path.tmp') . '/marketplace_service_download_test.zip';

        try {
            $request = $this->captureRequest(function (Service $service) use ($path) {
                $service->download('https://marketplace.example.test/api/2.0/plugins/FreePlugin/download/1.0.0', $path);
            });
        } finally {
            Filesystem::deleteFileIfExists($path);
        }

        $this->assertSame('GET', $request['method']);
        $this->assertEmpty($request['body']);
        $this->assertSame($path, $request['destinationPath']);
    }

    /**
     * @return array{url: string, method: string, body: mixed, destinationPath: string|null}
     */
    private function captureRequest(callable $sendRequest): array
    {
        $request = null;

        Piwik::addAction('Http.sendHttpRequest', function (
            string $url,
            array $httpEventParams,
            ?string &$response,
            ?int &$status,
            array &$headers
        ) use (&$request): void {
            $request = [
                'url' => $url,
                'method' => $httpEventParams['httpMethod'],
                'body' => $httpEventParams['body'],
                'destinationPath' => $httpEventParams['destinationPath'],
            ];

            $response = '{}';
            $status = 200;
        });

        // not a Marketplace host, so the fixture repository leaves the request to the listener above
        $sendRequest(new Service('https://marketplace.example.test'));

        $this->assertNotNull($request);

        return $request;
    }
}
