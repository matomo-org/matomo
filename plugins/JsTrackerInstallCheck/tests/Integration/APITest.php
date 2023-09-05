<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Plugins\JsTrackerInstallCheck\API;
use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;
use Piwik\Site;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group JsTrackerInstallCheck
 * @group Plugins
 * @group APITest
 */
class APITest extends JsTrackerInstallCheckIntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();

        Site::setSiteFromArray($this->idSite1, ['idSite' => $this->idSite1, 'main_url' => self::TEST_URL1]);
    }

    public function testWasJsTrackerInstallTestSuccessful()
    {
        $mock = $this->getMockBuilder(JsTrackerInstallCheck::class)->getMock();
        $mock->expects($this->any())->method('checkForJsTrackerInstallTestSuccess')->willReturnCallback(
            function (int $idSite, string $nonce) {
                return true;
            }
        );
        $this->api = new API($mock);

        $result = $this->api->wasJsTrackerInstallTestSuccessful($this->idSite1, self::TEST_NONCE1);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('isSuccess', $result);
        $this->assertTrue($result['isSuccess']);
        $this->assertArrayHasKey('mainUrl', $result);
        $this->assertSame(
            self::TEST_URL1,
            $result['mainUrl'],
            'The main URL of the site should have been included in the response'
        );
    }

    public function testInitiateJsTrackerInstallTest()
    {
        $mock = $this->getMockBuilder(JsTrackerInstallCheck::class)->getMock();
        $mock->expects($this->any())->method('initiateJsTrackerInstallTest')->willReturnCallback(
            function (int $idSite, string $url = '') {
                return [
                    'url' => self::TEST_URL1 . '?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . self::TEST_NONCE1,
                    'nonce' => self::TEST_NONCE1,
                ];
            }
        );
        $this->api = new API($mock);

        $result = $this->api->initiateJsTrackerInstallTest($this->idSite1);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('url', $result);
        $this->assertNotEmpty($result['url']);
        $this->assertArrayHasKey('nonce', $result);
        $this->assertNotEmpty($result['nonce']);
        $this->assertSame(
            self::TEST_URL1 . '?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $result['nonce'],
            $result['url']
        );
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
