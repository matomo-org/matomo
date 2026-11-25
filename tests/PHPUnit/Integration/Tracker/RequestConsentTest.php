<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;

/**
 * What a request reports is only half the answer: the site has to measure consent-aware
 * analytics, and a site that forces cookieless tracking measures every visitor the same way.
 *
 * @group Core
 * @group Tracker
 */
class RequestConsentTest extends IntegrationTestCase
{
    /** @var int */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Consent');
    }

    public function tearDown(): void
    {
        $this->setForcedCookielessTracking(false);

        parent::tearDown();
    }

    public function testASiteThatHasNotOptedInReportsNoConsent(): void
    {
        $this->assertFalse($this->buildRequest(['consent' => '1'])->hasConsent());
    }

    public function testAnOptedInSiteReportsTheConsentTheRequestCarries(): void
    {
        $this->optIn();

        $this->assertTrue($this->buildRequest(['consent' => '1'])->hasConsent());
        $this->assertFalse($this->buildRequest([])->hasConsent());
        $this->assertFalse($this->buildRequest(['consent' => '0'])->hasConsent());
    }

    /**
     * The answer is cached for the request, so changing the parameter has to clear it - otherwise
     * a plugin rewriting the request would get the answer for the value it replaced.
     */
    public function testChangingTheParameterChangesTheAnswer(): void
    {
        $this->optIn();

        $request = $this->buildRequest([]);
        $this->assertFalse($request->hasConsent());

        $request->setParam('consent', '1');
        $this->assertTrue($request->hasConsent());

        $request->setParam('consent', '0');
        $this->assertFalse($request->hasConsent());
    }

    public function testForcedCookielessTrackingReportsNoConsent(): void
    {
        $this->optIn();
        $this->setForcedCookielessTracking(true);

        $this->assertFalse($this->buildRequest(['consent' => '1'])->hasConsent());
    }

    private function buildRequest(array $params): Request
    {
        return new Request(array_merge(['idsite' => (string) $this->idSite, 'rec' => '1'], $params));
    }

    private function optIn(): void
    {
        $measurable = ConsentAwareAnalytics::getMeasurableSetting($this->idSite);
        $measurable->setValue(true);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    private function setForcedCookielessTracking(bool $enabled): void
    {
        $config = new PrivacyManagerConfig();
        $config->forceCookielessTracking = $enabled;

        Cache::deleteTrackerCache();
    }
}
