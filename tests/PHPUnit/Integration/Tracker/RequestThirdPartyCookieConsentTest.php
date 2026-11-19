<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Cookie;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Config\ThirdPartyCookies;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;

/**
 * A compliance policy requiring third-party cookies to be off keeps the cookie from every
 * visitor, including one who has consented. Consent lifts what the policy required and falls
 * back to what the admin configured: it never sets the cookie where the admin left it off.
 *
 * @group Core
 * @group Tracker
 */
class RequestThirdPartyCookieConsentTest extends IntegrationTestCase
{
    private const FIRST_PARTY_ID = '1111111111111111';
    private const SHARED_COOKIE_ID = 'abcdefabcdefabcd';

    /** @var int */
    private $idSite;

    /** @var array<string, mixed> */
    private $trackerConfigBackup;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Third-party cookie consent', 'http://example.com');
        $this->trackerConfigBackup = Config::getInstance()->Tracker;
        unset($_COOKIE['_pk_uid']);
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        Config::getInstance()->Tracker = $this->trackerConfigBackup;
        Config::getInstance()->{'Tracker_' . $this->idSite} = [];
        $config = new PrivacyManagerConfig();
        $config->forceCookielessTracking = false;
        unset($_COOKIE['_pk_uid']);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    /**
     * @dataProvider getResolutionCases
     */
    public function testConsentLiftsThePolicyBackToTheConfiguredValueOnly(
        bool $policy,
        bool $optedIn,
        ?int $globalValue,
        ?int $siteValue,
        array $consentParam,
        bool $expected
    ): void {
        $this->prepare($policy, $optedIn, $globalValue, $siteValue);

        $request = $this->buildRequest($consentParam);

        $this->assertSame($expected, (bool) $request->shouldUseThirdPartyCookieForConsent($request->hasConsent()));
    }

    /**
     * @return iterable<string, array{bool, bool, ?int, ?int, array<string, mixed>, bool}>
     */
    public function getResolutionCases(): iterable
    {
        $consent = ['consent' => '1'];

        yield 'policy, opted in, admin on, consent' => [true, true, 1, null, $consent, true];
        yield 'policy, opted in, admin on for the site, consent' => [true, true, null, 1, $consent, true];
        yield 'policy, opted in, admin off, consent' => [true, true, 0, null, $consent, false];
        yield 'policy, opted in, admin off for the site but on globally, consent' => [true, true, 1, 0, $consent, false];
        yield 'policy, opted in, admin unset, consent' => [true, true, null, null, $consent, false];
        yield 'policy, opted in, admin on, no consent' => [true, true, 1, null, [], false];
        yield 'policy, opted in, admin on, consent 0' => [true, true, 1, null, ['consent' => '0'], false];
        yield 'policy, opted in, admin on, consent empty' => [true, true, 1, null, ['consent' => ''], false];
        yield 'policy, opted in, admin on, consent true' => [true, true, 1, null, ['consent' => 'true'], false];
        yield 'policy, opted in, admin on, consent 2' => [true, true, 1, null, ['consent' => '2'], false];
        yield 'policy, opted in, admin on, consent with a trailing space' => [true, true, 1, null, ['consent' => '1 '], false];
        yield 'policy, opted in, admin on, consent as an array' => [true, true, 1, null, ['consent' => ['1']], false];
        yield 'policy, not opted in, admin on, consent' => [true, false, 1, null, $consent, false];
        // the unaffected state: without a policy the admin's value applies, consent or not
        yield 'no policy, opted in, admin on, consent' => [false, true, 1, null, $consent, true];
        yield 'no policy, opted in, admin on, no consent' => [false, true, 1, null, [], true];
        yield 'no policy, not opted in, admin on, no consent' => [false, false, 1, null, [], true];
        yield 'no policy, opted in, admin off, consent' => [false, true, 0, null, $consent, false];
    }

    public function testForcedCookielessTrackingKeepsThePolicyValue(): void
    {
        $this->prepare(true, true, 1, null);
        $config = new PrivacyManagerConfig();
        $config->forceCookielessTracking = true;
        Cache::deleteTrackerCache();

        $request = $this->buildRequest(['consent' => '1']);

        $this->assertFalse((bool) $request->shouldUseThirdPartyCookieForConsent($request->hasConsent()));
    }

    /**
     * @dataProvider getInvalidSiteCases
     */
    public function testARequestWithoutAValidSiteKeepsTheDefaultAnswer($idSite): void
    {
        $this->prepare(true, true, 1, null);

        $request = new Request(['idsite' => $idSite, 'rec' => '1', 'consent' => '1']);

        $this->assertFalse($request->hasConsent());
        $this->assertSame(
            (bool) $request->shouldUseThirdPartyCookie(),
            (bool) $request->shouldUseThirdPartyCookieForConsent($request->hasConsent())
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function getInvalidSiteCases(): iterable
    {
        yield 'zero' => ['0'];
        yield 'not numeric' => ['abc'];
    }

    /**
     * The request the JavaScript tracker file is served with has no site and reports no consent,
     * so it is answered as before.
     */
    public function testTheTrackerFileRequestIsAnsweredAsBefore(): void
    {
        $this->prepare(true, true, 1, null);

        $request = new Request([]);

        $this->assertSame(
            (bool) $request->shouldUseThirdPartyCookie(),
            (bool) $request->shouldUseThirdPartyCookieForConsent($request->hasConsent())
        );
    }

    /**
     * @dataProvider getWriteCases
     */
    public function testTheCookieIsWrittenOnlyWhereConsentLiftsThePolicy(
        bool $policy,
        bool $optedIn,
        int $adminValue,
        array $consentParam,
        bool $expectWritten
    ): void {
        $this->prepare($policy, $optedIn, $adminValue, null);

        $request = $this->buildCapturingRequest($consentParam);
        $this->trackWith($request);

        if ($expectWritten) {
            $this->assertSame([self::FIRST_PARTY_ID], $request->savedCookieValues);
        } else {
            $this->assertSame([], $request->savedCookieValues);
        }
    }

    /**
     * @return iterable<string, array{bool, bool, int, array<string, mixed>, bool}>
     */
    public function getWriteCases(): iterable
    {
        $consent = ['consent' => '1'];

        yield 'policy, opted in, admin on, consent' => [true, true, 1, $consent, true];
        yield 'policy, opted in, admin off, consent' => [true, true, 0, $consent, false];
        yield 'policy, opted in, admin on, no consent' => [true, true, 1, [], false];
        yield 'policy, not opted in, admin on, consent' => [true, false, 1, $consent, false];
        yield 'no policy, not opted in, admin on, no consent' => [false, false, 1, [], true];
        yield 'no policy, opted in, admin on, no consent' => [false, true, 1, [], true];
        yield 'no policy, opted in, admin off, consent' => [false, true, 0, $consent, false];
    }

    /**
     * The cookie is shared by every site tracked through this Matomo. A consented visitor who
     * already has one keeps its value rather than having it replaced by this site's visitor ID.
     */
    public function testAConsentedWriteKeepsTheSharedCookieValue(): void
    {
        $this->prepare(true, true, 1, null);
        $this->setExistingThirdPartyCookie(self::SHARED_COOKIE_ID);

        $request = $this->buildCapturingRequest(['consent' => '1']);
        $this->trackWith($request);

        $this->assertSame([self::SHARED_COOKIE_ID], $request->savedCookieValues);
    }

    /**
     * A visitor's identifier may not change with the consent a request reports, so the cookie
     * is not read back to recognise them where a policy keeps it off.
     */
    public function testRecognitionDoesNotChangeWithConsent(): void
    {
        $this->prepare(true, true, 1, null);
        $this->setExistingThirdPartyCookie(self::SHARED_COOKIE_ID);

        $withConsent = $this->buildRequest(['consent' => '1']);
        $withoutConsent = $this->buildRequest([]);

        $this->assertTrue($withConsent->hasConsent());
        $this->assertSame(self::FIRST_PARTY_ID, bin2hex((string) $withConsent->getVisitorId()));
        $this->assertSame(self::FIRST_PARTY_ID, bin2hex((string) $withoutConsent->getVisitorId()));
    }

    /**
     * The cookie follows the consent the request states, not the visit's: a later request in a
     * consented visit that does not repeat the flag leaves it as the consented one wrote it.
     */
    public function testARequestWithoutTheFlagInAConsentedVisitDoesNotWriteTheCookie(): void
    {
        $this->prepare(true, true, 1, null);

        $consented = $this->buildCapturingRequest(['consent' => '1']);
        $this->trackWith($consented);
        $this->assertSame([self::FIRST_PARTY_ID], $consented->savedCookieValues);

        $flagless = $this->buildCapturingRequest(['url' => 'http://example.com/second']);
        $this->trackWith($flagless);

        $this->assertSame([], $flagless->savedCookieValues);
        $this->assertSame(1, $this->countVisits(), 'the flagless request joined the consented visit');
    }

    /**
     * A consent ping landing on a visit measured before consent, once the visitor has moved on,
     * cannot start a visit and leaves the visit unconsented. It still writes the cookie, as the
     * consented visit the ping goes on to start would: the cookie is stored in the browser, not
     * measured against the visit.
     */
    public function testAConsentPingAfterNavigatingWritesTheCookieButLeavesTheVisitUnconsented(): void
    {
        $this->prepare(true, true, 1, null);

        $this->trackWith($this->buildCapturingRequest([]));
        $this->trackWith($this->buildCapturingRequest(['url' => 'http://example.com/second']));

        $ping = $this->buildCapturingRequest(['url' => 'http://example.com/second', 'ping' => '1', 'consent' => '1']);
        $this->trackWith($ping);

        $this->assertSame([self::FIRST_PARTY_ID], $ping->savedCookieValues);
        $this->assertSame(1, $this->countVisits(), 'a ping cannot start a visit');
        $this->assertEmpty(Db::fetchOne('SELECT consent FROM ' . Common::prefixTable('log_visit')));
    }

    public function testTheIgnoreCookieStillPreventsTheWrite(): void
    {
        $this->prepare(true, true, 1, null);
        $ignoreCookieName = Config::getInstance()->Tracker['ignore_visits_cookie_name'];
        $_COOKIE[$ignoreCookieName] = 'ignore=' . base64_encode('*');

        try {
            $request = $this->buildCapturingRequest(['consent' => '1']);
            $request->setThirdPartyCookie(hex2bin(self::FIRST_PARTY_ID));
        } finally {
            unset($_COOKIE[$ignoreCookieName]);
        }

        $this->assertSame([], $request->savedCookieValues);
    }

    public function testTheCompliancePageDescribesTheConsentAwareSettingOnlyForAnOptedInSite(): void
    {
        $this->assertSame(
            Piwik::translate('General_ThirdPartyCookieSettingWhatItDoes'),
            ThirdPartyCookies::getWhatItDoes($this->idSite)
        );
        $this->assertSame(
            Piwik::translate('General_ThirdPartyCookieSettingImpact'),
            ThirdPartyCookies::getImpact($this->idSite)
        );

        $this->optIn();

        $this->assertSame(
            Piwik::translate('General_ThirdPartyCookieSettingWhatItDoesConsentAware'),
            ThirdPartyCookies::getWhatItDoes($this->idSite)
        );
        $this->assertSame(
            Piwik::translate('General_ThirdPartyCookieSettingImpactConsentAware'),
            ThirdPartyCookies::getImpact($this->idSite)
        );
        $this->assertSame(
            Piwik::translate('General_ThirdPartyCookieSettingWhatItDoes'),
            ThirdPartyCookies::getWhatItDoes(null)
        );
    }

    private function prepare(bool $policy, bool $optedIn, ?int $globalValue, ?int $siteValue): void
    {
        $tracker = Config::getInstance()->Tracker;
        if (null === $globalValue) {
            unset($tracker['use_third_party_id_cookie']);
        } else {
            $tracker['use_third_party_id_cookie'] = $globalValue;
        }
        Config::getInstance()->Tracker = $tracker;

        if (null !== $siteValue) {
            Config::getInstance()->{'Tracker_' . $this->idSite} = ['use_third_party_id_cookie' => $siteValue];
        }

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, $policy, $this->idSite);

        if ($optedIn) {
            $this->optIn();
        }

        Cache::deleteTrackerCache();
    }

    private function optIn(): void
    {
        $measurable = ConsentAwareAnalytics::getMeasurableSetting($this->idSite);
        $measurable->setValue(true);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    private function setExistingThirdPartyCookie(string $idVisitor): void
    {
        $cookie = new Cookie('_pk_uid');
        // the cookie is keyed by position, as Request::setThirdPartyCookie() writes it
        $cookie->set('0', $idVisitor);
        $_COOKIE['_pk_uid'] = $cookie->generateContentString();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function buildRequest(array $params): Request
    {
        return new Request(array_merge($this->getBaseParams(), $params));
    }

    /**
     * @param array<string, mixed> $params
     */
    private function buildCapturingRequest(array $params): ThirdPartyCookieCapturingRequest
    {
        return new ThirdPartyCookieCapturingRequest(array_merge($this->getBaseParams(), $params));
    }

    /**
     * @return array<string, string>
     */
    private function getBaseParams(): array
    {
        return [
            'idsite' => (string) $this->idSite,
            'rec' => '1',
            'url' => 'http://example.com/landing',
            '_id' => self::FIRST_PARTY_ID,
        ];
    }

    private function countVisits(): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?', [$this->idSite]);
    }

    private function trackWith(Request $request): void
    {
        $visit = new Visit();
        $visit->setRequest($request);
        $visit->handle();
    }
}

/**
 * Records the value each third-party cookie would be saved with, instead of sending it.
 */
class ThirdPartyCookieCapturingRequest extends Request
{
    /** @var list<string> */
    public $savedCookieValues = [];

    protected function makeThirdPartyCookieUID()
    {
        $request = $this;

        return new class ($this->getCookieName(), $this->getCookieExpire(), $this->getCookiePath(), $request) extends Cookie {
            /** @var ThirdPartyCookieCapturingRequest */
            private $request;

            public function __construct($name, $expire, $path, ThirdPartyCookieCapturingRequest $request)
            {
                parent::__construct($name, $expire, $path);
                $this->request = $request;
            }

            public function save($sameSite = null)
            {
                $this->request->savedCookieValues[] = (string) $this->get(0);
            }
        };
    }
}
