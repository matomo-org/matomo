<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Api;

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

    public function test_fetch_throwsApiError_WhenMarketplaceReturnsAnError()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Service\Exception::class);
        $this->expectExceptionCode(101);
        $this->expectExceptionMessage('Requested plugin does not exist.');

        $this->service->returnFixture('v2.0_plugins_CustomPlugin1_info-access_token-notexistingtoken.json');
        $this->service->fetch('plugins/CustomPlugin1/info', array());
    }

    public function test_fetch_throwsHttpError_WhenMarketplaceReturnsNoResultWhichMeansHttpError()
    {
        $this->expectException(\Piwik\Plugins\Marketplace\Api\Service\Exception::class);
        $this->expectExceptionCode(100);
        $this->expectExceptionMessage('There was an error reading the response from the Marketplace');

        $this->service->setOnDownloadCallback(function () {
            return null;
        });
        $this->service->fetch('plugins/CustomPlugin1/info', array());
    }

    public function test_fetch_jsonDecodesTheHttpResponse()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-consumer1_paid2_custom1.json');
        $consumer = $this->service->fetch('consumer', array());
        $this->assertTrue(is_array($consumer));
        $this->assertNotEmpty($consumer);
    }
}
