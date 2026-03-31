<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\GeoIp2\tests\Integration;

use Piwik\Config;
use Piwik\Option;
use Piwik\Plugins\GeoIp2\Controller;
use Piwik\Plugins\GeoIp2\GeoIP2AutoUpdater;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group GeoIp2
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private $controller;
    private $server = [];
    private $get = [];
    private $post = [];
    private $request = [];

    public function setUp(): void
    {
        parent::setUp();

        Config::getInstance()->General['enable_geolocation_admin'] = 1;

        $this->controller = $this->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkTokenInUrl'])
            ->getMock();
        $this->controller->expects($this->any())
            ->method('checkTokenInUrl');

        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->request = $_REQUEST;

        Option::delete(GeoIP2AutoUpdater::LOC_URL_OPTION_NAME);
        Option::delete(GeoIP2AutoUpdater::ISP_URL_OPTION_NAME);
        Option::delete('geoip2.download_url.loc');
        Option::delete('geoip2.download_url.isp');
    }

    public function tearDown(): void
    {
        $_SERVER = $this->server;
        $_GET = $this->get;
        $_POST = $this->post;
        $_REQUEST = $this->request;

        Option::delete(GeoIP2AutoUpdater::LOC_URL_OPTION_NAME);
        Option::delete(GeoIP2AutoUpdater::ISP_URL_OPTION_NAME);
        Option::delete('geoip2.download_url.loc');
        Option::delete('geoip2.download_url.isp');

        parent::tearDown();
    }

    public function testDownloadMissingGeoIpDbShouldAbortIfConfiguredUrlChangesMidDownloadAndDeleteChunks()
    {
        $oldLocUrl = 'https://download.db-ip.com/free/dbip-city-lite-2020-01.mmdb.gz';
        $newLocUrl = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=test&suffix=tar.gz';
        $ispUrl = 'https://download.db-ip.com/free/dbip-asn-lite-2020-01.mmdb.gz';

        Option::set(GeoIP2AutoUpdater::LOC_URL_OPTION_NAME, $oldLocUrl);
        Option::set(GeoIP2AutoUpdater::ISP_URL_OPTION_NAME, $ispUrl);
        Option::set('geoip2.download_url.loc', $oldLocUrl);
        Option::set('geoip2.download_url.isp', $ispUrl);

        $locDownloadChunk = GeoIP2AutoUpdater::getTemporaryFolder(
            GeoIP2AutoUpdater::getZippedFilenameToDownloadTo(
                $oldLocUrl,
                'loc',
                GeoIP2AutoUpdater::getGeoIPUrlExtension($oldLocUrl)
            ),
            true
        );
        $ispDownloadChunk = GeoIP2AutoUpdater::getTemporaryFolder(
            GeoIP2AutoUpdater::getZippedFilenameToDownloadTo(
                $ispUrl,
                'isp',
                GeoIP2AutoUpdater::getGeoIPUrlExtension($ispUrl)
            ),
            true
        );

        file_put_contents($locDownloadChunk, 'loc');
        file_put_contents($ispDownloadChunk, 'isp');
        Option::set($locDownloadChunk . '_expectedDownloadSize', '12');
        Option::set($ispDownloadChunk . '_expectedDownloadSize', '12');

        Option::set(GeoIP2AutoUpdater::LOC_URL_OPTION_NAME, $newLocUrl);

        $this->setPostRequest(1, 'loc');
        $continueResponse = $this->decodeJsonResponse($this->controller->downloadMissingGeoIpDb());

        $this->assertArrayHasKey('error', $continueResponse);
        $this->assertFileNotExists($locDownloadChunk);
        $this->assertFileExists($ispDownloadChunk);
        $this->assertFalse(Option::get($locDownloadChunk . '_expectedDownloadSize'));
        $this->assertSame('12', Option::get($ispDownloadChunk . '_expectedDownloadSize'));
        $this->assertFalse(Option::get('geoip2.download_url.loc'));

        @unlink($ispDownloadChunk);
        Option::delete($ispDownloadChunk . '_expectedDownloadSize');
    }

    private function setPostRequest(int $continue, string $key): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [
            'continue' => $continue,
            'key' => $key,
        ];
        $_POST = $_GET;
        $_REQUEST = $_GET;
    }

    private function decodeJsonResponse($response): array
    {
        return (array) json_decode((string) $response, true);
    }
}
