<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable\tests\Integration\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\IntranetMeasurable\Tracker\RequestProcessor;
use Piwik\Plugins\IntranetMeasurable\Type;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;

/**
 * @group IntranetMeasurable
 * @group RequestProcessorTest
 * @group Plugins
 */
class RequestProcessorTest extends IntegrationTestCase
{
    private const SETTING_NAME = 'ini.Tracker.trust_visitors_cookies';

    /** @var int */
    private $idIntranetSite;

    /** @var int */
    private $idRegularSite;

    /** @var RequestProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->idIntranetSite = Fixture::createWebsite(
            '2020-01-01 00:00:00',
            $ecommerce = 0,
            $siteName = false,
            $siteUrl = false,
            $siteSearch = 1,
            $searchKeywordParameters = null,
            $searchCategoryParameters = null,
            $timezone = null,
            Type::ID
        );
        $this->idRegularSite = Fixture::createWebsite('2020-01-01 00:00:00');

        // make sure the tracker cache reflects the intranet type for the freshly created sites
        Cache::regenerateCacheWebsiteAttributes([$this->idIntranetSite, $this->idRegularSite]);

        $this->processor = new RequestProcessor();

        // start each test from the default (cookies not trusted globally)
        $this->setTrustCookiesSetting(0);
    }

    public function tearDown(): void
    {
        $this->setTrustCookiesSetting(0);
        parent::tearDown();
    }

    public function testManipulateRequestKeepsTrustCookiesEnabledForConsecutiveIntranetRequests(): void
    {
        // first intranet request enables the setting
        $this->processor->manipulateRequest($this->makeRequest($this->idIntranetSite));
        $this->assertSame(1, $this->getTrustCookiesSetting());

        // second intranet request (e.g. bulk tracking) must keep it enabled - it previously reset to 0
        $this->processor->manipulateRequest($this->makeRequest($this->idIntranetSite));
        $this->assertSame(1, $this->getTrustCookiesSetting());
    }

    public function testManipulateRequestResetsTrustCookiesWhenSwitchingToNonIntranetSite(): void
    {
        $this->processor->manipulateRequest($this->makeRequest($this->idIntranetSite));
        $this->assertSame(1, $this->getTrustCookiesSetting());

        // a following non-intranet request in the same run resets what we enabled
        $this->processor->manipulateRequest($this->makeRequest($this->idRegularSite));
        $this->assertSame(0, $this->getTrustCookiesSetting());
    }

    public function testManipulateRequestDoesNotResetSettingItDidNotEnable(): void
    {
        // setting already enabled globally - the processor must not take ownership of it
        $this->setTrustCookiesSetting(1);

        $this->processor->manipulateRequest($this->makeRequest($this->idIntranetSite));
        $this->assertSame(1, $this->getTrustCookiesSetting());

        // switching to a non-intranet site must leave the pre-existing setting untouched
        $this->processor->manipulateRequest($this->makeRequest($this->idRegularSite));
        $this->assertSame(1, $this->getTrustCookiesSetting());
    }

    private function makeRequest(int $idSite): Request
    {
        return new Request(['idsite' => $idSite]);
    }

    private function getTrustCookiesSetting(): int
    {
        return (int) StaticContainer::get(self::SETTING_NAME);
    }

    private function setTrustCookiesSetting(int $value): void
    {
        StaticContainer::getContainer()->set(self::SETTING_NAME, $value);
    }
}
