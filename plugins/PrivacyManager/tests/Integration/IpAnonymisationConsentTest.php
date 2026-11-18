<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Matomo\Network\IPUtils;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * A compliance policy enforcing IP anonymisation masks every visitor's IP address, including one
 * who has consented. Consent lifts what the policy enforced back to what the admin configured for
 * the site themselves, and nothing more.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class IpAnonymisationConsentTest extends IntegrationTestCase
{
    private const IP = '192.168.10.20';

    /** @var int */
    private $idSite;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    /** @var list<bool> */
    private $consentPassedToTheEvent = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'IP consent', 'http://example.com');
        $this->testDate = Date::factory('2024-01-02 10:00:00');
        $this->tracker = $this->makeTracker($useLocal = false);
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    /**
     * @return iterable<string, array{0: bool, 1: bool, 2: ?string, 3: bool, 4: int, 5: string, 6: string}>
     */
    public function getStoredIpCases(): iterable
    {
        // policy, opted in, consent, admin's anonymisation, admin's mask length, IP, stored IP
        yield 'no consent keeps the policy mask' => [true, true, null, false, 2, self::IP, '192.168.0.0'];
        yield 'consent lifts the policy where the admin anonymises nothing' => [true, true, '1', false, 2, self::IP, self::IP];
        yield 'consent lifts the policy to the admin\'s shorter mask' => [true, true, '1', true, 1, self::IP, '192.168.10.0'];
        yield 'consent does not go past the admin\'s own mask equal to the policy' => [true, true, '1', true, 2, self::IP, '192.168.0.0'];
        yield 'consent keeps the admin\'s longer mask' => [true, true, '1', true, 3, self::IP, '192.0.0.0'];
        yield 'consent=0 is not consent' => [true, true, '0', false, 2, self::IP, '192.168.0.0'];
        yield 'consent=2 is not consent' => [true, true, '2', false, 2, self::IP, '192.168.0.0'];
        yield 'consent=true is not consent' => [true, true, 'true', false, 2, self::IP, '192.168.0.0'];
        yield 'an empty consent is not consent' => [true, true, '', false, 2, self::IP, '192.168.0.0'];
        yield 'a site that has not opted in keeps the policy mask' => [true, false, '1', false, 2, self::IP, '192.168.0.0'];
        yield 'no policy and no anonymisation stores the full IP' => [false, true, null, false, 2, self::IP, self::IP];
        yield 'no policy keeps the admin\'s own mask without consent' => [false, true, null, true, 2, self::IP, '192.168.0.0'];
        yield 'no policy keeps the admin\'s own mask with consent' => [false, true, '1', true, 2, self::IP, '192.168.0.0'];
        yield 'no policy and not opted in keeps the admin\'s own mask' => [false, false, '1', true, 1, self::IP, '192.168.10.0'];
        yield 'IPv6 without consent keeps the policy mask' => [true, true, null, false, 2, '2001:db8:85a3::8a2e:370:7334', '2001:db8:85a3::'];
        yield 'IPv6 with consent is stored in full' => [true, true, '1', false, 2, '2001:db8:85a3::8a2e:370:7334', '2001:db8:85a3::8a2e:370:7334'];
    }

    /**
     * @dataProvider getStoredIpCases
     */
    public function testTheStoredIpFollowsConsentOnlyWherePolicyAndOptInAllowIt(
        bool $policy,
        bool $optedIn,
        ?string $consent,
        bool $adminAnonymises,
        int $adminMaskLength,
        string $ip,
        string $expectedIp
    ): void {
        $this->configureSite($policy, $optedIn, $adminAnonymises, $adminMaskLength);

        $this->trackPageView($consent, 'landing', $ip);

        $this->assertSame(1, $this->countVisits());
        $this->assertSame($expectedIp, $this->fetchStoredIp());
    }

    /**
     * A site forcing cookieless tracking measures every visitor the same way, so consent relaxes
     * nothing there either.
     */
    public function testConsentDoesNotLiftTheMaskOnASiteThatForcesCookielessTracking(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);
        $globalConfig = new PrivacyManagerConfig();
        $globalConfig->forceCookielessTracking = true;

        $this->trackPageView('1');

        $this->assertSame('192.168.0.0', $this->fetchStoredIp());
    }

    /**
     * Consent given once the visitor has moved on starts a visit of its own. That visit is the
     * consented one; the visit before it keeps the mask it was measured with.
     */
    public function testTheVisitConsentStartsIsStoredInFullAndTheEarlierOneStaysMasked(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);

        $this->trackPageView(null, 'landing');
        $this->trackPageView('1', 'other');

        $this->assertSame(2, $this->countVisits(), 'consent away from the arrival page starts its own visit');
        $this->assertSame('192.168.0.0', $this->fetchStoredIp(0));
        $this->assertSame(self::IP, $this->fetchStoredIp(1));
    }

    /**
     * A visit forced to start after a consented one, by a request without consent, is not the
     * consented visit. The full IP stored for the consented visit must not carry over into it.
     */
    public function testAVisitForcedWithoutConsentAfterAConsentedOneIsMasked(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);

        $this->trackPageView('1', 'landing?mtm_campaign=a');
        $this->trackPageView(null, 'landing?mtm_campaign=b', '10.20.30.40');

        $this->assertSame(2, $this->countVisits(), 'a new campaign starts a new visit');
        $this->assertSame(self::IP, $this->fetchStoredIp(0));
        $this->assertNotSame(self::IP, $this->fetchStoredIp(1));
        $this->assertStringEndsWith('.0.0', $this->fetchStoredIp(1));
    }

    /**
     * Requests for different visitors sent together are each measured by their own consent.
     */
    public function testBulkRequestsAreEachMeasuredByTheirOwnConsent(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);

        $this->tracker->enableBulkTracking();

        $this->prepareRequest('1', 'landing', self::IP);
        $this->tracker->setVisitorId('1111111111111111');
        $this->tracker->doTrackPageView('Landing');

        $this->prepareRequest(null, 'landing', '192.168.10.21');
        $this->tracker->setVisitorId('2222222222222222');
        $this->tracker->doTrackPageView('Landing');

        Fixture::checkBulkTrackingResponse($this->tracker->doBulkTrack());

        $this->assertSame(2, $this->countVisits());
        $this->assertSame(self::IP, $this->fetchStoredIp(0));
        $this->assertSame('192.168.0.0', $this->fetchStoredIp(1));
    }

    /**
     * Consent belongs to the visit, so a later request in a consented visit that does not repeat
     * the flag is still treated as consented.
     */
    public function testALaterRequestInAConsentedVisitIsTreatedAsConsentedWithoutTheFlag(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);
        $this->captureTheConsentPassedToTheEvent();

        $this->trackPageView('1', 'landing');
        $this->trackPageView(null, 'other');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame([true, true], $this->consentPassedToTheEvent);
        $this->assertSame(self::IP, $this->fetchStoredIp());
    }

    /**
     * A consent ping landing on a visit measured before consent, once the visitor has moved on,
     * cannot start a visit and may not be used on the one it lands on.
     */
    public function testAConsentingPingAfterNavigatingIsNotTreatedAsConsentedOnTheEarlierVisit(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);
        $this->captureTheConsentPassedToTheEvent();

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->ping('1', 'other');

        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertSame([false, false, false], $this->consentPassedToTheEvent);
        $this->assertSame('192.168.0.0', $this->fetchStoredIp());
    }

    /**
     * Without consent, the listener is told so, whatever the site's configuration.
     */
    public function testARequestWithoutConsentIsNotTreatedAsConsented(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);
        $this->captureTheConsentPassedToTheEvent();

        $this->trackPageView(null);

        $this->assertSame([false], $this->consentPassedToTheEvent);
    }

    /**
     * @return iterable<string, array{0: bool, 1: bool, 2: bool, 3: int, 4: ?string, 5: string, 6: ?int, 7?: string}>
     */
    public function getArrivalPageCases(): iterable
    {
        // policy, opted in, admin's anonymisation, admin's mask length, consent on the second
        // request, stored IP, stored consent
        yield 'consent on the arrival page lifts the policy for the visit' => [true, true, false, 2, '1', self::IP, 1];
        yield 'consent on the arrival page lifts it only to the admin\'s own mask' => [true, true, true, 1, '1', '192.168.10.0', 1];
        yield 'consent on the arrival page does not go past the admin\'s own mask' => [true, true, true, 2, '1', '192.168.0.0', 1];
        yield 'a second request without consent changes nothing' => [true, true, false, 2, null, '192.168.0.0', null];
        yield 'a site that has not opted in changes nothing' => [true, false, false, 2, '1', '192.168.0.0', null];
        yield 'with no policy the admin\'s own mask still applies' => [false, true, true, 2, '1', '192.168.0.0', 1];
        yield 'with no policy consent from another address does not rewrite the IP' => [false, true, true, 2, '1', '192.168.0.0', 1, '10.20.30.40'];
        yield 'consent from another address on the arrival page is the IP written' => [true, true, false, 2, '1', '10.20.30.40', 1, '10.20.30.40'];
    }

    /**
     * Consent given while the visitor is still on the page they arrived on continues the visit
     * measured before it, and that visit is the consented one from then on. The IP stored for it
     * is lifted as the visit's own consent allows, as it would have been had consent come first.
     *
     * @dataProvider getArrivalPageCases
     */
    public function testConsentOnTheArrivalPageLiftsTheIpOfTheVisitItContinues(
        bool $policy,
        bool $optedIn,
        bool $adminAnonymises,
        int $adminMaskLength,
        ?string $consent,
        string $expectedIp,
        ?int $expectedConsent,
        string $secondIp = self::IP
    ): void {
        $this->configureSite($policy, $optedIn, $adminAnonymises, $adminMaskLength);

        $this->trackPageView(null, 'landing');
        $this->assertSame($policy || $adminAnonymises ? '192.168.0.0' : self::IP, $this->fetchStoredIp(), 'precondition');

        $this->trackPageView($consent, 'landing', $secondIp);

        $this->assertSame(1, $this->countVisits(), 'the visit is continued, not split');
        $this->assertSame($expectedIp, $this->fetchStoredIp());
        $this->assertSame($expectedConsent, $this->fetchStoredConsent());
    }

    /**
     * Only the request bringing consent into the visit writes its IP. A later request in the
     * consented visit, even from another address, leaves it as the visit started being measured.
     */
    public function testALaterRequestInAConsentedVisitDoesNotRewriteTheIp(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);

        $this->trackPageView(null, 'landing');
        $this->trackPageView('1', 'landing');
        $this->trackPageView('1', 'landing', '192.168.10.99');
        $this->trackPageView(null, 'landing', '192.168.10.98');

        $this->assertSame(1, $this->countVisits());
        $this->assertSame(self::IP, $this->fetchStoredIp());
    }

    /**
     * Each site's own admin values are the ones consent lifts its anonymisation to.
     */
    public function testConsentLiftsEachSitesAnonymisationToThatSitesOwnValues(): void
    {
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = false, $adminMaskLength = 2);

        $firstSite = $this->idSite;
        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'IP consent, second site', 'http://example.com');
        $this->configureSite($policy = true, $optedIn = true, $adminAnonymises = true, $adminMaskLength = 1);

        $this->tracker = $this->makeTracker($useLocal = false);
        $this->trackPageView('1');

        $secondSite = $this->idSite;
        $this->idSite = $firstSite;
        $this->tracker = $this->makeTracker($useLocal = false);
        $this->trackPageView('1');

        $this->assertSame(self::IP, $this->fetchStoredIp());

        $this->idSite = $secondSite;
        $this->assertSame('192.168.10.0', $this->fetchStoredIp());

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $firstSite);
    }

    private function configureSite(bool $policy, bool $optedIn, bool $adminAnonymises, int $adminMaskLength): void
    {
        $config = new PrivacyManagerConfig($this->idSite);
        $config->ipAnonymizerEnabled = $adminAnonymises;
        $config->ipAddressMaskLength = $adminMaskLength;

        if ($optedIn) {
            $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
            $measurable->setValue(true);
            $measurable->save();
        }

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, $policy, $this->idSite);
        Cache::deleteTrackerCache();
    }

    /**
     * Only an in-process tracker can be observed, so the requests are tracked locally from here on.
     */
    private function captureTheConsentPassedToTheEvent(): void
    {
        $this->tracker = $this->makeTracker($useLocal = true);

        Piwik::addAction('Tracker.setVisitorIp', function (&$ip, $idSite, $hasConsent = null) {
            $this->consentPassedToTheEvent[] = $hasConsent;
        });
    }

    private function makeTracker(bool $useLocal): \MatomoTracker
    {
        $tracker = Fixture::getTracker($this->idSite, $this->testDate->getDatetime(), $defaultInit = true, $useLocal);
        $tracker->setUrlReferrer('');

        return $tracker;
    }

    private function ping(?string $consent, string $page): void
    {
        $this->prepareRequest($consent, $page, self::IP);

        Fixture::checkResponse($this->tracker->doPing());
    }

    private function trackPageView(?string $consent, string $page = 'landing', string $ip = self::IP): void
    {
        $this->prepareRequest($consent, $page, $ip);

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function prepareRequest(?string $consent, string $page, string $ip): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();
        $this->tracker->setIp($ip);

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->setUrl('http://example.com/' . $page);
    }

    private function fetchStoredIp(int $offset = 0): string
    {
        $ip = Db::fetchOne(
            'SELECT location_ip FROM ' . Common::prefixTable('log_visit')
            . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1 OFFSET ' . $offset,
            [$this->idSite]
        );

        return IPUtils::binaryToStringIP($ip);
    }

    private function fetchStoredConsent(): ?int
    {
        $consent = Db::fetchOne(
            'SELECT consent FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1',
            [$this->idSite]
        );

        return null === $consent ? null : (int) $consent;
    }

    private function countVisits(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    /**
     * Tracking at a fixed past date, and from a given IP, is only accepted on an authenticated request.
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
