<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Option;
use Piwik\Plugins\JsTrackerInstallCheck\API;
use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group JsTrackerInstallCheck
 * @group Plugins
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var JsTrackerInstallCheck
     */
    protected $jsTrackerInstallCheck;

    /**
     * @var int
     */
    private $idSite1;

    /**
     * @var int
     */
    private $idSite2;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();
        $this->jsTrackerInstallCheck = new JsTrackerInstallCheck();

        $this->idSite1 = Fixture::createWebsite('2014-01-01 00:00:00');
        $this->idSite2 = Fixture::createWebsite('2014-01-01 00:00:00');
    }

    /**
     * @dataProvider getCheckForJsTrackerInstallTestSuccessTests
     * @param bool $createNonce Indicates whether to create a nonce option row
     * @param bool $setSuccess Indicates whether to mark the nonce option as successful
     * @param int $idSite The site to check for the nonce
     * @param string $nonceValue If left empty, the created nonce will be used when looking up the nonce. If not, the
     * provided value will be used to look up the nonce.
     * @param bool $isSuccess Indicates whether the returned array should indicate that the nonce was successful or not
     * @return void
     * @throws \Exception
     */
    public function testCheckForJsTrackerInstallTestSuccess(bool $createNonce, bool $setSuccess, int $siteNum, string $nonceValue, bool $isSuccess)
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

        if (!empty($nonceValue)) {
            $nonce = $nonceValue;
            $nonceProvidedString = "checking for created nonce";
        }

        $result = $this->api->checkForJsTrackerInstallTestSuccess($idSite, $nonce);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('isSuccess', $result);
        $this->assertSame($isSuccess, $result['isSuccess'], "Expected $isSuccess for site $idSite where $nonceCreatedString, $nonceSuccessString, and $nonceProvidedString.");
    }

    private function getCheckForJsTrackerInstallTestSuccessTests(): array
    {
        return [
            [false, false, 1, 'abc123', false],
            [true, false, 1, 'abc123', false],
            [true, true, 1, 'abc123', false],
            [true, false, 1, '', false],
            [true, true, 1, '', true],
            [true, true, 2, '', false],
        ];
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

    private function createNonceOption(int $idSite): string {
        $initiateResult = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest((string) $idSite);
        $this->assertIsArray($initiateResult);
        $this->assertArrayHasKey('nonce', $initiateResult);
        $this->assertNotEmpty($initiateResult['nonce']);

        return $initiateResult['nonce'];
    }

    private function setNonceCheckAsSuccessful(int $idSite) {
        $optionKey = JsTrackerInstallCheck::OPTION_NAME_PREFIX . $idSite;
        $option = Option::get($optionKey);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('isSuccessful', $decodedOption);

        $decodedOption['isSuccessful'] = true;
        Option::set($optionKey, json_encode($decodedOption));
    }
}
