<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Api;

use Piwik\Cache;
use Piwik\Plugin;
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

    public function setUp()
    {
        parent::setUp();

        $this->service = new TestService();
    }

    /**
     * @expectedException \Piwik\Plugins\Marketplace\Api\Service\Exception
     * @expectedExceptionCode 101
     * @expectedExceptionMessage Requested plugin does not exist.
     */
    public function test_fetch_throwsApiError_WhenMarketplaceReturnsAnError()
    {
        $this->service->returnFixture('v2.0_plugins_CustomPlugin1_info-access_token-notexistingtoken.json');
        $this->service->fetch('plugins/CustomPlugin1/info', array());
    }

    /**
     * @expectedException \Piwik\Plugins\Marketplace\Api\Service\Exception
     * @expectedExceptionCode 100
     * @expectedExceptionMessage There was an error reading the response from the Marketplace
     */
    public function test_fetch_throwsHttpError_WhenMarketplaceReturnsNoResultWhichMeansHttpError()
    {
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
