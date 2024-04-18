<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\JsTrackerInstallCheck\NonceOption\JsTrackerInstallCheckOption;

class JsTrackerInstallCheckOptionTest extends TestCase
{
    public const TEST_URL1 = 'https://some-test-site.local';
    public const TEST_URL2 = 'https://another-test-site.local';
    public const TEST_URL3 = 'https://nonexistent-test-site.local';
    public const TEST_NONCE1 = '7fa8282ad93047a4d6fe6111c93b308a';
    public const TEST_NONCE2 = '79d886010186eb60e3611cd4a5d0bcae';

    /**
     * @var JsTrackerInstallCheckOption
     */
    protected $jsTrackerInstallCheckOption;

    protected $optionsArray;

    protected $site1Nonces;

    public function setUp(): void
    {
        parent::setUp();

        $this->jsTrackerInstallCheckOption = new JsTrackerInstallCheckOption();

        $this->site1Nonces = [
            self::TEST_NONCE1 => [
                JsTrackerInstallCheckOption::NONCE_DATA_TIME => Date::getNowTimestamp(),
                JsTrackerInstallCheckOption::NONCE_DATA_URL => self::TEST_URL1,
                JsTrackerInstallCheckOption::NONCE_DATA_IS_SUCCESS => true,
            ],
            self::TEST_NONCE2 => [
                JsTrackerInstallCheckOption::NONCE_DATA_TIME => Date::getNowTimestamp(),
                JsTrackerInstallCheckOption::NONCE_DATA_URL => self::TEST_URL2,
                JsTrackerInstallCheckOption::NONCE_DATA_IS_SUCCESS => false,
            ],
        ];
        $this->optionsArray = [
            JsTrackerInstallCheckOption::OPTION_NAME_PREFIX . 1 => json_encode($this->site1Nonces)
        ];
        $mock = $this->getMockBuilder('stdClass')
            ->addMethods(['getValue', 'setValue'])
            ->getMock();
        $mock->expects($this->any())->method('getValue')->willReturnCallback(function ($key) {
            return $this->optionsArray[$key] ?? false;
        });
        $mock->expects($this->any())->method('setValue')->willReturnCallback(function ($key, $value) {
            $this->optionsArray[$key] = $value;
        });

        Option::setSingletonInstance($mock);
    }

    public function tearDown(): void
    {
        Option::setSingletonInstance(null);
    }

    public function testGetNonceMap()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceMap(1);
        $this->assertSame($this->site1Nonces, $result);
    }

    public function testGetNonceMapNoSiteNonces()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceMap(2);
        $this->assertSame([], $result);
    }

    public function testGetNonceMapInvalidJson()
    {
        $this->optionsArray[JsTrackerInstallCheckOption::OPTION_NAME_PREFIX . 2] = '{"test": "test"';
        $result = $this->jsTrackerInstallCheckOption->getNonceMap(2);
        $this->assertSame([], $result);
    }

    public function testGetCurrentNonceMap()
    {
        $result = $this->jsTrackerInstallCheckOption->getCurrentNonceMap(1);
        $this->assertSame($this->site1Nonces, $result);
    }

    public function testGetCurrentNonceMapWithUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getCurrentNonceMap(1, self::TEST_URL1);
        $this->assertCount(1, $result);
        $testArray = $this->site1Nonces;
        // Remove the second nonce from the collection
        unset($testArray[self::TEST_NONCE2]);
        $this->assertSame($testArray, $result);
    }

    public function testGetCurrentNonceMapWithUnusedUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getCurrentNonceMap(1, self::TEST_URL3);
        $this->assertSame([], $result);
    }

    public function testGetCurrentNonceMapWithExpiredNonce()
    {
        // Make the first nonce old enough to be expired
        $this->site1Nonces[self::TEST_NONCE1]['time'] = Date::getNowTimestamp() - 400;
        $this->optionsArray[JsTrackerInstallCheckOption::OPTION_NAME_PREFIX . 1] = json_encode($this->site1Nonces);
        $result = $this->jsTrackerInstallCheckOption->getCurrentNonceMap(1);
        $this->assertCount(1, $result);
        $testArray = $this->site1Nonces;
        // Remove the first nonce from the collection
        unset($testArray[self::TEST_NONCE1]);
        $this->assertSame($testArray, $result);
    }

    public function testLookUpNonceNoSite()
    {
        $result = $this->jsTrackerInstallCheckOption->lookUpNonce(2, '');
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testLookUpNonceEmptyNonce()
    {
        $result = $this->jsTrackerInstallCheckOption->lookUpNonce(1, '');
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testLookUpNonceNonexistentNonce()
    {
        $result = $this->jsTrackerInstallCheckOption->lookUpNonce(1, 'abc123');
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testLookUpNonce()
    {
        $result = $this->jsTrackerInstallCheckOption->lookUpNonce(1, self::TEST_NONCE1);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertGreaterThan(0, $result['time']);
        $this->assertArrayHasKey('url', $result);
        $this->assertSame(self::TEST_URL1, $result['url']);
        $this->assertArrayHasKey('isSuccessful', $result);
        $this->assertTrue($result['isSuccessful']);
    }

    public function testLookUpNonceAlternateNonce()
    {
        $result = $this->jsTrackerInstallCheckOption->lookUpNonce(1, self::TEST_NONCE2);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertGreaterThan(0, $result['time']);
        $this->assertArrayHasKey('url', $result);
        $this->assertSame(self::TEST_URL2, $result['url']);
        $this->assertArrayHasKey('isSuccessful', $result);
        $this->assertFalse($result['isSuccessful']);
    }

    public function testGetNonceForSiteAndUrlEmptyUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, '');
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetNonceForSiteAndUrlNonexistentNonce()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, self::TEST_URL3);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetNonceForSiteAndUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, self::TEST_URL1);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(self::TEST_NONCE1, $result);
        $data = $result[self::TEST_NONCE1];
        $this->assertArrayHasKey('time', $data);
        $this->assertGreaterThan(0, $data['time']);
        $this->assertArrayHasKey('url', $data);
        $this->assertSame(self::TEST_URL1, $data['url']);
        $this->assertArrayHasKey('isSuccessful', $data);
        $this->assertTrue($data['isSuccessful']);
    }

    public function testGetNonceForSiteAndUrlAlternateUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, self::TEST_URL2);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(self::TEST_NONCE2, $result);
        $data = $result[self::TEST_NONCE2];
        $this->assertArrayHasKey('time', $data);
        $this->assertGreaterThan(0, $data['time']);
        $this->assertArrayHasKey('url', $data);
        $this->assertSame(self::TEST_URL2, $data['url']);
        $this->assertArrayHasKey('isSuccessful', $data);
        $this->assertFalse($data['isSuccessful']);
    }

    public function testGetNonceForSiteAndUrlNoSite()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(2, self::TEST_URL1);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testMarkNonceAsSuccessFul()
    {
        $this->assertFalse($this->isNonceSuccessFul(1, self::TEST_NONCE2));
        $this->assertTrue($this->jsTrackerInstallCheckOption->markNonceAsSuccessFul(1, self::TEST_NONCE2));
        $this->assertTrue($this->isNonceSuccessFul(1, self::TEST_NONCE2));
    }

    public function testcreateNewNonce()
    {
        $this->assertCount(2, $this->getTestOptionArray(1));
        $result = $this->jsTrackerInstallCheckOption->createNewNonce(1, self::TEST_URL3);
        $this->assertNotEmpty($result);
        $this->assertNotContains($result, [self::TEST_NONCE1, self::TEST_NONCE2], 'The nonce should be new');
        $optionArray = $this->getTestOptionArray(1);
        $this->assertCount(3, $optionArray);
        $this->assertArrayHasKey($result, $optionArray);
    }

    public function testcreateNewNonceExistingNonce()
    {
        $initialTime = $this->site1Nonces[self::TEST_NONCE1][JsTrackerInstallCheckOption::NONCE_DATA_TIME];
        $this->assertCount(2, $this->getTestOptionArray(1));
        // Increase the now timestamp so that we can confirm that the time of the nonce was updated.
        Date::$now = Date::getNowTimestamp() + 5;
        $result = $this->jsTrackerInstallCheckOption->createNewNonce(1, self::TEST_URL1);
        $this->assertNotEmpty($result);
        $this->assertSame(self::TEST_NONCE1, $result);
        $optionArray = $this->getTestOptionArray(1);
        $this->assertCount(2, $optionArray);
        $this->assertGreaterThan($initialTime, $optionArray[$result][JsTrackerInstallCheckOption::NONCE_DATA_TIME]);
    }

    /**
     * Helper method for checking the test nonces
     *
     * @param int $idSite
     * @return array
     */
    protected function getTestOptionArray(int $idSite): array
    {
        $optionKey = JsTrackerInstallCheckOption::OPTION_NAME_PREFIX . $idSite;
        $this->assertArrayHasKey($optionKey, $this->optionsArray);
        $option = $this->optionsArray[$optionKey];
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);

        return $decodedOption;
    }

    /**
     * Helper method for checking the test nonce
     *
     * @param int $idSite
     * @param string $nonce
     * @return array
     */
    protected function getTestNonceArray(int $idSite, string $nonce): array
    {
        $decodedOption = $this->getTestOptionArray($idSite);

        return $decodedOption[$nonce];
    }

    /**
     * Helper method for checking if the nonce is successful
     *
     * @param int $idSite
     * @param string $nonce
     * @return bool
     */
    protected function isNonceSuccessFul(int $idSite, string $nonce): bool
    {
        $nonceArray = $this->getTestNonceArray($idSite, $nonce);

        return !empty($nonceArray[JsTrackerInstallCheckOption::NONCE_DATA_IS_SUCCESS]);
    }
}
