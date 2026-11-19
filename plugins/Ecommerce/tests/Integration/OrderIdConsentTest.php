<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Ecommerce\tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\Ecommerce\Settings\OrderIdAnonymization;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics as ConsentAwareAnalyticsSetting;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * A compliance policy requiring order IDs to be anonymised anonymises them for every visitor,
 * including one who has consented. Consent lifts what the policy required, and nothing more.
 *
 * @group Ecommerce
 * @group Plugins
 */
class OrderIdConsentTest extends IntegrationTestCase
{
    private const ORDER_ID = 'ORDER-123';

    /** @var int */
    private $idSite;

    /** @var Date */
    private $testDate;

    /** @var \MatomoTracker */
    private $tracker;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', $ecommerce = 1, 'Order ID consent', 'http://example.com');
        $this->testDate = Date::factory('2024-01-02 10:00:00');
        $this->tracker = $this->makeTracker();
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        Cache::deleteTrackerCache();

        parent::tearDown();
    }

    /**
     * @dataProvider getOrdersTrackedOnASingleRequest
     */
    public function testOrderIdIsStoredAsTheVisitsConsentAndTheSettingsRequire(
        bool $policy,
        bool $optedIn,
        ?string $consent,
        array $adminValues,
        string $orderId,
        bool $expectStoredAsSent
    ): void {
        $this->configure($policy, $optedIn, $adminValues);

        $this->trackOrder($consent, $orderId);

        $this->assertOrderId($expectStoredAsSent, $orderId);
    }

    public function getOrdersTrackedOnASingleRequest(): iterable
    {
        $noAdmin = [];

        // positive control: the policy's anonymisation is lifted for a visitor who consented
        yield 'policy, opted in, consent' => [true, true, '1', $noAdmin, self::ORDER_ID, true];
        // stored exactly as a site without the policy stores it, sanitised once like any tracking parameter
        yield 'policy, opted in, consent, markup in the ID' => [true, true, '1', $noAdmin, '<b>"O\'1"</b>', true];
        yield 'no policy, markup in the ID' => [false, true, null, $noAdmin, '<b>"O\'1"</b>', true];

        yield 'policy, opted in, no consent' => [true, true, null, $noAdmin, self::ORDER_ID, false];
        yield 'policy, opted in, consent=0' => [true, true, '0', $noAdmin, self::ORDER_ID, false];
        yield 'policy, opted in, consent=2' => [true, true, '2', $noAdmin, self::ORDER_ID, false];
        yield 'policy, opted in, consent=true' => [true, true, 'true', $noAdmin, self::ORDER_ID, false];

        // a site that has not opted in treats every visitor the same, as before
        yield 'policy, not opted in, consent' => [true, false, '1', $noAdmin, self::ORDER_ID, false];

        // the admin's own value is not the policy's to lift, nor consent's
        yield 'policy, opted in, consent, admin on for the site' => [true, true, '1', ['site' => true], self::ORDER_ID, false];
        yield 'no policy, opted in, consent, admin on for the site' => [false, true, '1', ['site' => true], self::ORDER_ID, false];
        yield 'policy, opted in, consent, admin on for the instance, off for the site' => [true, true, '1', ['global' => true, 'site' => false], self::ORDER_ID, false];

        // with nothing enforced there is nothing for consent to lift
        yield 'no policy, opted in, no consent' => [false, true, null, $noAdmin, self::ORDER_ID, true];
        yield 'no policy, opted in, consent' => [false, true, '1', $noAdmin, self::ORDER_ID, true];
        yield 'no policy, not opted in, no consent' => [false, false, null, $noAdmin, self::ORDER_ID, true];
    }

    public function testOrderIdIsAnonymisedForAConsentingVisitorWhileCookielessTrackingIsForced(): void
    {
        $this->configure(true, true, []);
        $config = new PrivacyManagerConfig();
        $config->forceCookielessTracking = true;
        Cache::deleteTrackerCache();

        $this->trackOrder('1');

        $this->assertOrderId(false);
    }

    /**
     * Consent belongs to the visit: an order placed after the visitor consented on an earlier
     * page is stored as sent, though the order request does not repeat the flag.
     */
    public function testAnOrderLaterInAConsentedVisitIsStoredAsSent(): void
    {
        $this->configure(true, true, []);

        $this->trackPageView('1');
        $this->trackOrder(null);

        $this->assertSame(1, $this->countVisits(), 'the order should belong to the consented visit');
        $this->assertOrderId(true);
    }

    /**
     * A visit does not inherit the consent of the visit before it.
     */
    public function testAnOrderStartingANewVisitDoesNotInheritThePreviousVisitsConsent(): void
    {
        $this->configure(true, true, []);

        $this->trackPageView('1');
        $this->testDate = $this->testDate->addHour(2);
        $this->trackOrder(null);

        $this->assertSame(2, $this->countVisits(), 'the order should have started a visit of its own');
        $this->assertOrderId(false);
    }

    /**
     * Turning consent-aware analytics off takes effect for visits that already consented.
     */
    public function testAnOrderInAConsentedVisitIsAnonymisedOnceTheSiteStopsMeasuringConsent(): void
    {
        $this->configure(true, true, []);

        $this->trackPageView('1');
        $this->setOptedIn(false);
        $this->trackOrder(null);

        $this->assertSame(1, $this->countVisits());
        $this->assertOrderId(false);
    }

    /**
     * An order ID stored as sent is unique per site, as on any site that does not anonymise them.
     */
    public function testTheSameConsentedOrderIdIsRecordedOnce(): void
    {
        $this->configure(true, true, []);

        $this->trackOrder('1');
        $this->tracker->doTrackEcommerceOrder(self::ORDER_ID, 10);

        $this->assertSame(1, $this->countOrders());
        $this->assertOrderId(true);
    }

    /**
     * Each request in a bulk request is decided on its own visit, and the order ID kept for one
     * is not carried over to the next.
     */
    public function testEachOrderInABulkRequestFollowsItsOwnVisitsConsent(): void
    {
        $this->configure(true, true, []);

        $this->tracker->enableBulkTracking();

        $this->addOrderToBulk('1111111111111111', '1', 'ORDER-1');
        $this->addOrderToBulk('2222222222222222', null, 'ORDER-2');
        $this->addOrderToBulk('3333333333333333', '1', 'ORDER-3');

        Fixture::checkBulkTrackingResponse($this->tracker->doBulkTrack());

        $orderIds = $this->fetchOrderIds();
        $this->assertCount(3, $orderIds);
        $this->assertSame('ORDER-1', $orderIds[0]);
        $this->assertAnonymised('ORDER-2', $orderIds[1]);
        $this->assertSame('ORDER-3', $orderIds[2]);
    }

    /**
     * Consent reported by an order placed after the visitor moved on from the page they arrived
     * on belongs to the visit the next page view will start, not to the one measured before it,
     * so the order stays anonymised on that one - as the User ID stays withheld from it.
     *
     * @dataProvider getPoliciesEnforcingTheOrderIdAnonymisation
     */
    public function testAConsentingOrderAfterNavigatingDoesNotLiftTheAnonymisationOnTheEarlierVisit(string $policy): void
    {
        $this->enforce($policy);

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->trackOrder('1');

        $this->assertSame(1, $this->countVisits(), 'an order cannot start a visit');
        $this->assertNotSame('1', (string) $this->fetchVisitColumn('consent'), 'the earlier visit took the consent');
        $this->assertOrderId(false);
    }

    /**
     * A page view reporting consent once the visitor has moved on starts the consented visit,
     * and the order placed in it is stored as sent.
     *
     * @dataProvider getPoliciesEnforcingTheOrderIdAnonymisation
     */
    public function testAnOrderInTheVisitConsentStartedAfterNavigatingIsStoredAsSent(string $policy): void
    {
        $this->enforce($policy);

        $this->trackPageView(null, 'landing');
        $this->trackPageView(null, 'other');
        $this->trackPageView('1', 'third');
        $this->trackOrder(null);

        $this->assertSame(2, $this->countVisits(), 'consent should have started a visit of its own');
        $this->assertOrderId(true);
    }

    /**
     * Consent arriving on the page the visit arrived on continues that visit.
     *
     * @dataProvider getPoliciesEnforcingTheOrderIdAnonymisation
     */
    public function testAConsentingOrderOnTheLandingVisitIsStoredAsSent(string $policy): void
    {
        $this->enforce($policy);

        $this->trackPageView(null, 'landing');
        $this->trackOrder('1', self::ORDER_ID, 'landing');

        $this->assertSame(1, $this->countVisits());
        $this->assertOrderId(true);
    }

    public function getPoliciesEnforcingTheOrderIdAnonymisation(): iterable
    {
        yield 'CNIL policy' => ['cnil'];
        // a granular policy enforcing the order ID anonymisation and nothing else
        yield 'order ID anonymisation enforced alone' => ['orderIdOnly'];
    }

    /**
     * The compliance page describes the setting as applying to visitors who have not consented
     * only on a site that measures consent-aware analytics.
     */
    public function testTheSettingIsDescribedForConsentOnlyOnAnOptedInSite(): void
    {
        $this->setOptedIn(false);

        $this->assertSame(Piwik::translate('Ecommerce_OrderIdAnonymizationSettingWhatItDoes'), OrderIdAnonymization::getWhatItDoes($this->idSite));
        $this->assertSame(Piwik::translate('Ecommerce_OrderIdAnonymizationSettingImpact'), OrderIdAnonymization::getImpact($this->idSite));
        $this->assertSame(Piwik::translate('Ecommerce_OrderIdAnonymizationSettingImpact'), OrderIdAnonymization::getImpact(null));

        $this->setOptedIn(true);

        $this->assertSame(Piwik::translate('Ecommerce_OrderIdAnonymizationSettingWhatItDoesConsentAware'), OrderIdAnonymization::getWhatItDoes($this->idSite));
        $this->assertSame(Piwik::translate('Ecommerce_OrderIdAnonymizationSettingImpactConsentAware'), OrderIdAnonymization::getImpact($this->idSite));
    }

    private function addOrderToBulk(string $visitorId, ?string $consent, string $orderId): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->setVisitorId($visitorId);
        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }

        $this->tracker->doTrackEcommerceOrder($orderId, 10);
    }

    private function configure(bool $policy, bool $optedIn, array $adminValues): void
    {
        if (array_key_exists('global', $adminValues)) {
            $config = new PrivacyManagerConfig();
            $config->anonymizeOrderId = $adminValues['global'];
        }

        if (array_key_exists('site', $adminValues)) {
            $config = new PrivacyManagerConfig($this->idSite);
            $config->anonymizeOrderId = $adminValues['site'];
        }

        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, $policy, $this->idSite);

        $this->setOptedIn($optedIn);
    }

    private function enforce(string $policy): void
    {
        if ('cnil' === $policy) {
            $this->configure(true, true, []);
            return;
        }

        $this->configure(false, true, []);
        OrderIdAnonymization::setEnforced(true, $this->idSite);
        Cache::deleteTrackerCache();
    }

    private function setOptedIn(bool $optedIn): void
    {
        $measurable = ConsentAwareAnalyticsSetting::getMeasurableSetting($this->idSite);
        $measurable->setValue($optedIn);
        $measurable->save();

        Cache::deleteTrackerCache();
    }

    private function trackPageView(?string $consent, string $page = 'landing'): void
    {
        $this->prepareRequest($consent);
        $this->tracker->setUrl('http://example.com/' . $page);

        Fixture::checkResponse($this->tracker->doTrackPageView(ucfirst($page)));
    }

    private function trackOrder(?string $consent, string $orderId = self::ORDER_ID, ?string $page = null): void
    {
        $this->prepareRequest($consent);

        if (null !== $page) {
            $this->tracker->setUrl('http://example.com/' . $page);
        }

        Fixture::checkResponse($this->tracker->doTrackEcommerceOrder($orderId, 10));
    }

    private function prepareRequest(?string $consent): void
    {
        $this->testDate = $this->testDate->addPeriod(1, 'minute');
        $this->tracker->setForceVisitDateTime($this->testDate->getDatetime());
        $this->tracker->clearCustomTrackingParameters();

        if (null !== $consent) {
            $this->tracker->setCustomTrackingParameter('consent', $consent);
        }
    }

    private function assertOrderId(bool $expectStoredAsSent, string $sent = self::ORDER_ID): void
    {
        $orderIds = $this->fetchOrderIds();
        $this->assertCount(1, $orderIds, 'one order should have been recorded');

        if ($expectStoredAsSent) {
            $this->assertSame(Common::sanitizeInputValue($sent), $orderIds[0], 'the order ID should be stored as sent');
        } else {
            $this->assertAnonymised($sent, $orderIds[0]);
        }
    }

    private function assertAnonymised(string $sent, string $stored): void
    {
        $this->assertNotSame($sent, $stored, 'the order ID should have been anonymised');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $stored, 'the order ID should have been anonymised');
    }

    /**
     * @return string[]
     */
    private function fetchOrderIds(): array
    {
        $rows = Db::fetchAll(
            'SELECT idorder FROM ' . Common::prefixTable('log_conversion')
            . ' WHERE idsite = ? AND idorder IS NOT NULL ORDER BY idvisit ASC, server_time ASC',
            [$this->idSite]
        );

        return array_column($rows, 'idorder');
    }

    private function countOrders(): int
    {
        return count($this->fetchOrderIds());
    }

    /**
     * @return mixed
     */
    private function fetchVisitColumn(string $column)
    {
        return Db::fetchOne(
            'SELECT `' . $column . '` FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ? ORDER BY idvisit ASC LIMIT 1',
            [$this->idSite]
        );
    }

    private function countVisits(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    private function makeTracker(): \MatomoTracker
    {
        $tracker = Fixture::getTracker(
            $this->idSite,
            $this->testDate->getDatetime(),
            $defaultInit = true,
            $useLocal = false
        );
        $tracker->setUrlReferrer('');

        return $tracker;
    }

    /**
     * Tracking at a fixed past date is only accepted on an authenticated request.
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
