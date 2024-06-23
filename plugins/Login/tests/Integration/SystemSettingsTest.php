<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Plugins\Login\SystemSettings;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group BruteForceDetection
 */
class SystemSettingsTest extends IntegrationTestCase
{
    /**
     * @var SystemSettings
     */
    private $settings;

    private $exampleIps = array(
        '12.12.12.12/27',
        '14.14.14.14',
        '15.15.15.*',
        '2001:db8::/40',
        '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
    );

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = new SystemSettings();
    }

    public function testEnableBruteForceDetectionIsEnabledByDefault()
    {
        $this->assertTrue($this->settings->enableBruteForceDetection->getValue());
    }

    public function testLoginAttemptsTimeRangeHasCorrectDefaultValue()
    {
        $this->assertSame(60, $this->settings->loginAttemptsTimeRange->getValue());
    }

    public function testMaxFailedLoginsPerMinutesHasCorrectDefaultValue()
    {
        $this->assertSame(20, $this->settings->maxFailedLoginsPerMinutes->getValue());
    }

    public function testWhitelisteBruteForceIpsHasNoIpWhitelisted()
    {
        $this->assertSame([], $this->settings->whitelisteBruteForceIps->getValue());
    }

    public function testWhitelisteBruteForceIpsCanSuccessfullySetVariousIpsAndRanges()
    {
        $this->settings->whitelisteBruteForceIps->setValue($this->exampleIps);
        $this->assertSame($this->exampleIps, $this->settings->whitelisteBruteForceIps->getValue());
    }

    public function testWhitelisteBruteForceIpsFailsWhenContainsInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SitesManager_ExceptionInvalidIPFormat');

        $this->settings->whitelisteBruteForceIps->setValue(array(
            '127.0.0.1', 'foobar'
        ));
    }

    public function testIsWhitelistedIpDoesNotWhitelistAnyIpsByDefault()
    {
        $this->assertFalse($this->settings->isWhitelistedIp('127.0.0.1'));
    }

    /**
     * @dataProvider getIpListedDataProvider
     */
    public function testIsWhitelistedIpIsIpInList($expected, $ip)
    {
        $this->settings->whitelisteBruteForceIps->setValue($this->exampleIps);
        $this->assertSame($expected, $this->settings->isWhitelistedIp($ip));
        $this->assertFalse($this->settings->isBlacklistedIp($ip));
    }

    public function testBlacklistedBruteForceIpsHasNoIpWhitelisted()
    {
        $this->assertSame([], $this->settings->blacklistedBruteForceIps->getValue());
    }

    public function testBlacklistedBruteForceIpsCanSuccessfullySetVariousIpsAndRanges()
    {
        $this->settings->blacklistedBruteForceIps->setValue($this->exampleIps);
        $this->assertSame($this->exampleIps, $this->settings->blacklistedBruteForceIps->getValue());
    }

    public function testBlacklistedBruteForceIpsFailsWhenContainsInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SitesManager_ExceptionInvalidIPFormat');

        $this->settings->blacklistedBruteForceIps->setValue(array(
            '127.0.0.1', 'foobar'
        ));
    }

    /**
     * @dataProvider getIpListedDataProvider
     */
    public function testIsBlacklistedIpIsIpInList($expected, $ip)
    {
        $this->settings->blacklistedBruteForceIps->setValue($this->exampleIps);
        $this->assertSame($expected, $this->settings->isBlacklistedIp($ip));
        $this->assertFalse($this->settings->isWhitelistedIp($ip));
    }

    public function getIpListedDataProvider()
    {
        return array(
            array(true, '12.12.12.14'),
            array(true, '12.12.12.31'),
            array(true, '14.14.14.14'),
            array(true, '15.15.15.123'),
            array(true, '2001:0db8:85a3:0000:0000:8a2e:0370:7334'),

            array(false, ''),
            array(false, null),
            array(false, '12.12.12.32'),
            array(false, '14.14.14.12'),
            array(false, '2001:0db8:85a3:0000:0000:8a2e:0370:7333'),
        );
    }

    public function testIsBlacklistedIpDoesNotWhitelistAnyIpsByDefault()
    {
        $this->assertFalse($this->settings->isBlacklistedIp('127.0.0.1'));
    }
}
