<?php

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Option;
use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class JsTrackerInstallCheckIntegrationTestCase extends IntegrationTestCase
{
    /**
     * @var JsTrackerInstallCheck
     */
    protected $jsTrackerInstallCheck;

    /**
     * @var int
     */
    protected $idSite1;

    /**
     * @var int
     */
    protected $idSite2;

    public function setUp(): void
    {
        parent::setUp();
        $this->jsTrackerInstallCheck = new JsTrackerInstallCheck();

        $this->idSite1 = Fixture::createWebsite('2014-01-01 00:00:00');
        $this->idSite2 = Fixture::createWebsite('2014-01-01 00:00:00');
    }

    protected function createNonceOption(int $idSite): string
    {
        $initiateResult = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest((string) $idSite);
        $this->assertIsArray($initiateResult);
        $this->assertArrayHasKey('nonce', $initiateResult);
        $this->assertNotEmpty($initiateResult['nonce']);

        return $initiateResult['nonce'];
    }

    protected function setNonceCheckAsSuccessful(int $idSite): void
    {
        $optionKey = JsTrackerInstallCheck::OPTION_NAME_PREFIX . $idSite;
        $option = Option::get($optionKey);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('isSuccessful', $decodedOption);

        $decodedOption['isSuccessful'] = true;
        Option::set($optionKey, json_encode($decodedOption));
    }

    protected function setNonceCheckTimestamp(int $idSite, int $timestamp): void
    {
        $optionKey = JsTrackerInstallCheck::OPTION_NAME_PREFIX . $idSite;
        $option = Option::get($optionKey);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('time', $decodedOption);

        $decodedOption['time'] = $timestamp;
        Option::set($optionKey, json_encode($decodedOption));
    }

    protected function getOptionForSite(int $idSite)
    {
        return Option::get(JsTrackerInstallCheck::OPTION_NAME_PREFIX . $idSite);
    }

    protected function isNonceForSiteSuccessFul(int $idSite): bool
    {
        $option = $this->getOptionForSite($idSite);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('isSuccessful', $decodedOption);

        return !empty($decodedOption['isSuccessful']);
    }
}