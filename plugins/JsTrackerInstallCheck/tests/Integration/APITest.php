<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Plugins\JsTrackerInstallCheck\API;
use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;
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
    }

    /**
     * @dataProvider getWasJsTrackerInstallTestSuccessful
     * @param bool $createNonce Indicates whether to create a nonce option row
     * @param bool $setSuccess Indicates whether to mark the nonce option as successful
     * @param int $siteNum The site to check for the nonce
     * @param string $nonceValue If left empty, the created nonce will be used when looking up the nonce. If not, the
     * provided value will be used to look up the nonce.
     * @param bool $isSuccess Indicates whether the returned array should indicate that the nonce was successful or not
     * @return void
     * @throws \Exception
     */
    public function testWasJsTrackerInstallTestSuccessful(bool $createNonce, bool $setSuccess, int $siteNum, string $nonceValue, bool $isSuccess)
    {
        $nonce = '';
        $idSite = $siteNum === 1 ? $this->idSite1 : $this->idSite2;
        $nonceCreatedString = 'nonce not created';
        $nonceSuccessString = 'nonce not successful';
        $nonceProvidedString = "checking for $nonceValue nonce";

        // Generate the nonce and store it in the option table
        if ($createNonce) {
            $nonce = $this->createNonceOption($this->idSite1);
            $nonceCreatedString = 'nonce was created';
        }
        // Set the option nonce to successful
        if ($setSuccess) {
            $this->setNonceCheckAsSuccessful($this->idSite1);
            $nonceSuccessString = 'nonce was successful';
        }

        if (!empty($nonceValue) && $nonceValue !== 'generated') {
            $nonce = $nonceValue;
        } elseif (!empty($nonceValue)) {
            $nonceProvidedString = "checking for generated nonce";
        }

        $result = $this->api->wasJsTrackerInstallTestSuccessful($idSite, $nonce);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('isSuccess', $result);
        $this->assertSame($isSuccess, $result['isSuccess'], "Expected $isSuccess for site $idSite where $nonceCreatedString, $nonceSuccessString, and $nonceProvidedString.");
    }

    public function testInitiateJsTrackerInstallTest()
    {
        // Should return false because the option doesn't exist yet
        $option = $this->getOptionForSite($this->idSite1);
        $this->assertFalse($option);

        $result = $this->api->initiateJsTrackerInstallTest($this->idSite1);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('url', $result);
        $this->assertNotEmpty($result['url']);
        $this->assertArrayHasKey('nonce', $result);
        $this->assertNotEmpty($result['nonce']);
        $this->assertSame('http://piwik.net?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $result['nonce'], $result['url']);

        $option = $this->getOptionForSite($this->idSite1);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertSame($result['nonce'], $decodedOption['nonce']);
        $this->assertFalse($decodedOption['isSuccessful']);
    }

    private function getWasJsTrackerInstallTestSuccessful(): array
    {
        return [
            [false, false, 1, 'abc123', false],
            [false, false, 1, '', false],
            [false, false, 1, 'generated', false],
            [true, false, 1, 'abc123', false],
            [true, false, 1, '', false],
            [true, false, 1, 'generated', false],
            [true, true, 1, 'abc123', false],
            [true, true, 1, '', true],
            [true, true, 1, 'generated', true],
            [false, false, 2, 'abc123', false],
            [false, false, 2, '', false],
            [false, false, 2, 'generated', false],
            [true, false, 2, 'abc123', false],
            [true, false, 2, '', false],
            [true, false, 2, 'generated', false],
            [true, true, 2, 'abc123', false],
            [true, true, 2, '', false],
            [true, true, 2, 'generated', false],
        ];
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
