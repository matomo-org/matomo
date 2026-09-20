<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\VisitExcluded;
use Piwik\Version;

/**
 * @group TrackingSpamPrevention
 * @group Plugins
 */
class TrackingSpamPreventionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-12-12 01:02:03');
        Fixture::createSuperUser();

        $this->setBlockClouds(true);
        StaticContainer::get(BlockedIpRanges::class)->updateBlockedIpRanges();
    }

    private function makeExcluded($ip)
    {
        $req = new Request(['idsite' => 1, 'cip' => $ip, 'token_auth' => Fixture::getTokenAuth(), 'rec' => 1]);
        return new VisitExcluded($req);
    }

    public function testTrackerCache()
    {
        $cache = Cache::getCacheGeneral();
        $this->assertEquals([
            '10.' => ['10.10.0.0/21'],
            '200.' => ['200.200.0.0/21'],
        ], $cache[BlockedIpRanges::OPTION_KEY]);
    }

    public function testIsExcludedVisitWhenBlockedUserAgentWhenGoodUserAgentWontBlock()
    {
        StaticContainer::get(SystemSettings::class)->blockHeadless->setValue(1);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitWhenBlockedUserAgent()
    {
        StaticContainer::get(SystemSettings::class)->blockHeadless->setValue(1);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/79.0.3945.0 Safari/537.36';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertTrue($isExcluded);
    }

    public function testIsExcludedVisitWhenHeadlessClientHint()
    {
        StaticContainer::get(SystemSettings::class)->blockHeadless->setValue(1);
        Cache::clearCacheGeneral();

        // set normal browser not a headless browser
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.0 Safari/537.36';
        $excluded = $this->makeExcluded('22.22.22.22');
        $excluded->request->setParam('uadata', '{"fullVersionList":[{"brand":"Not A(Brand","version":"99.0.0.0"},{"brand":"HeadlessChrome","version":"121.0.6167.57"},{"brand":"Chromium","version":"121.0.6167.57"}],"mobile":false,"model":"","platform":"Linux","platformVersion":"5.15.0"}');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertTrue($isExcluded);
    }

    public function testIsExcludedVisitWhenBlockedUserAgentDisabled()
    {
        StaticContainer::get(SystemSettings::class)->blockHeadless->setValue(0);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/79.0.3945.0 Safari/537.36';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitWhenNothingBlocked()
    {
        StaticContainer::get(BlockedIpRanges::class)->unsetAllIpRanges();
        $excluded = $this->makeExcluded('10.10.0.3');
        $this->assertFalse($excluded->isExcluded());
    }

    public function testIsExcludedVisitWhenBlockServerSideLibraryDisabledAndNotServerSideUserAgent()
    {
        StaticContainer::get(SystemSettings::class)->blockServerSideLibraries->setValue(0);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitWhenBlockServerSideLibraryDisabledAndServerSideUserAgent()
    {
        StaticContainer::get(SystemSettings::class)->blockServerSideLibraries->setValue(0);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'curl/7.68.0';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitWhenBlockServerSideLibraryEnabledAndNotServerSideUserAgent()
    {
        StaticContainer::get(SystemSettings::class)->blockServerSideLibraries->setValue(1);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitWhenBlockServerSideLibraryEnabledAndServerSideUserAgent()
    {
        StaticContainer::get(SystemSettings::class)->blockServerSideLibraries->setValue(1);
        Cache::clearCacheGeneral();

        $_SERVER['HTTP_USER_AGENT'] = 'curl/7.68.0';
        $excluded = $this->makeExcluded('22.22.22.22');
        $isExcluded = $excluded->isExcluded();
        unset($_SERVER['HTTP_USER_AGENT']);
        if (version_compare(Version::VERSION, '4.7.0-b1', '>=') || version_compare(PHP_VERSION, '8.0.0', '<')) {
            $this->assertTrue($isExcluded);
        } else {
            $this->assertFalse($isExcluded);
        }
    }

    public function testIsExcludedVisitWhenIpBlocked()
    {
        $excluded = $this->makeExcluded('10.10.0.3');
        $this->assertTrue($excluded->isExcluded());
        $excluded = $this->makeExcluded('200.200.0.1');
        $this->assertTrue($excluded->isExcluded());
    }

    public function testIsExcludedVisitWhenWhiteListUsed()
    {
        StaticContainer::get(SystemSettings::class)->ipAllowList->setValue([
            '10.10.0.4/32', '10.10.0.3/32',
        ]);
        $excluded = $this->makeExcluded('10.10.0.2');
        $this->assertTrue($excluded->isExcluded());

        $excluded = $this->makeExcluded('10.10.0.3');
        $this->assertFalse($excluded->isExcluded());

        $excluded = $this->makeExcluded('10.10.0.4');
        $this->assertFalse($excluded->isExcluded());

        $excluded = $this->makeExcluded('10.10.0.5');
        $this->assertTrue($excluded->isExcluded());
    }

    public function testIsExcludedVisitWhenIpNotBlocked()
    {
        $excluded = $this->makeExcluded('20.20.20.20');
        $this->assertFalse($excluded->isExcluded());
    }

    public function testIsExcludedVisitWhenIpOnBlockList()
    {
        StaticContainer::get(SystemSettings::class)->ipBlockList->setValue([
            '203.0.113.88/32', '198.51.100.0/24',
        ]);

        $this->assertTrue($this->makeExcluded('203.0.113.88')->isExcluded());
        $this->assertTrue($this->makeExcluded('198.51.100.55')->isExcluded());

        $this->assertFalse($this->makeExcluded('203.0.113.89')->isExcluded());
        $this->assertFalse($this->makeExcluded('198.51.101.1')->isExcluded());
    }

    public function testIsExcludedVisitAllowListTakesPrecedenceOverBlockList()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->ipAllowList->setValue(['203.0.113.88/32']);
        $settings->ipBlockList->setValue(['203.0.113.0/24']);

        $this->assertFalse($this->makeExcluded('203.0.113.88')->isExcluded());
        $this->assertTrue($this->makeExcluded('203.0.113.89')->isExcluded());
    }

    public function testIsExcludedVisitExcludeCountries()
    {
        StaticContainer::get(SystemSettings::class)->excludedCountries->setValue(
            [['country' => 'xx'],['country' => 'fr'], ['country' => 'nz'], ['country' => 'de'], ['country' => 'us']]
        );

        $excluded = $this->makeExcluded('127.0.0.1');
        $this->assertTrue($excluded->isExcluded());

        StaticContainer::get(SystemSettings::class)->excludedCountries->setValue(
            [['country' => 'ai']]
        );

        $excluded = $this->makeExcluded('127.0.0.1');
        $this->assertFalse($excluded->isExcluded());
    }

    public function testIsExcludedVisitIncludeCountries()
    {
        StaticContainer::get(SystemSettings::class)->includedCountries->setValue(
            [['country' => 'xx'],['country' => 'fr'], ['country' => 'nz'], ['country' => 'de'], ['country' => 'us']]
        );

        $excluded = $this->makeExcluded('127.0.0.1');
        $this->assertFalse($excluded->isExcluded());

        StaticContainer::get(SystemSettings::class)->includedCountries->setValue(
            [['country' => 'ai']]
        );

        $excluded = $this->makeExcluded('127.0.0.1');
        $this->assertTrue($excluded->isExcluded());
    }

    private function setBlockClouds($val)
    {
        StaticContainer::get(SystemSettings::class)->block_clouds->setValue($val);
        Cache::clearCacheGeneral();
    }
}
