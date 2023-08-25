<?php

namespace Piwik\Tests\Piwik\Plugins\JsTrackerInstallCheck\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\JsTrackerInstallCheck\NonceOption\JsTrackerInstallCheckOption;

class JsTrackerInstallCheckOptionTest extends TestCase
{
    protected $jsTrackerInstallCheckOption;

    public function setUp(): void
    {
        parent::setUp();

        $this->jsTrackerInstallCheckOption = new JsTrackerInstallCheckOption();
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
        $result = $this->jsTrackerInstallCheckOption->lookUpNonce(1, '1111111111');
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertGreaterThan(0, $result['time']);
        $this->assertArrayHasKey('url', $result);
        $this->assertSame('https://some-test-site.local', $result['url']);
        $this->assertArrayHasKey('isSuccessful', $result);
        $this->assertTrue($result['url']);
    }

    public function testGetNonceForSiteAndUrlEmptyUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, '');
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetNonceForSiteAndUrlNonexistentNonce()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, 'https://another-test-site.local');
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetNonceForSiteAndUrl()
    {
        $result = $this->jsTrackerInstallCheckOption->getNonceForSiteAndUrl(1, '1111111111');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('time', $result);
        $this->assertGreaterThan(0, $result['time']);
        $this->assertArrayHasKey('url', $result);
        $this->assertSame('https://some-test-site.local', $result['url']);
        $this->assertArrayHasKey('isSuccessful', $result);
        $this->assertTrue($result['url']);
    }
}
