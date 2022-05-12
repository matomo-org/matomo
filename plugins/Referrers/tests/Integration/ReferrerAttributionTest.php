<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

/**
 * @group Referrers
 * @group ReferrerAttribution
 * @group Plugins
 */
class ReferrerAttributionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 0); // set to default
        $env->save();
    }

    /**
     * Test Case 1:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * not using referrer attribution cookies
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     * 2. Return from external (payment) provider
     *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
     *    --> visit now attributed to external (payment) provider
     * 3. Ecommerce conversion
     *   --> conversion attributed to external (payment) provider
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWillUpdateReferrer()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to direct entry
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // no new visit, but attributed referer should now be updated to payment provider
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_WEBSITE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to payment provider
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_WEBSITE), $conversions[0]);
    }

    /**
     * Test Case 2:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * not using referrer attribution cookies
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     * 2. Return from external (payment) provider
     *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
     *    --> visit still attributed to search engine
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWillKeepReferrer()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to search engine
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 3:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * not using referrer attribution cookies
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     * 2. Return from external (payment) provider
     *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
     *    --> the new visit will be attributed to external (payment) provider
     * 3. Ecommerce conversion
     *    --> conversion (of second visit) attributed to external (payment) provider
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithNewVisitWhenWebsiteReferrerChangesWillCreateNewVisit()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to direct entry
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // A new visit should have been created attributed to the payment provider
        $visits = $this->getVisitReferrers();

        self::assertEquals(2, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);
        self::assertEquals($this->buildVisit(2, $idSite, 1, Common::REFERRER_TYPE_WEBSITE), $visits[1]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // Check conversion is attributed to payment provider
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('2', $idSite, Common::REFERRER_TYPE_WEBSITE), $conversions[0]);
    }

    /**
     * Test Case 4:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * not using referrer attribution cookies
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     * 2. Return from external (payment) provider
     *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
     *    --> the new visit will be attributed to external (payment) provider
     * 3. Ecommerce conversion
     *    --> conversion (of second visit) attributed to external (payment) provider
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithNewVisitWhenWebsiteReferrerChangesWillCreateNewVisit()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // a new visit will be created due to changed referrer and `create_new_visit_when_website_referrer_changes = 1`
        $visits = $this->getVisitReferrers();

        self::assertEquals(2, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);
        self::assertEquals($this->buildVisit(2, $idSite, 1, Common::REFERRER_TYPE_WEBSITE), $visits[1]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to payment provider
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('2', $idSite, Common::REFERRER_TYPE_WEBSITE), $conversions[0]);
    }

    /**
     * Test Case 5:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * not using referrer attribution cookies
     * external (payment) provider added as site url
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     * 3. Ecommerce conversion
     *    --> conversion attributed to direct entry
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithPaymentProviderAddedAsSiteUrlWillNotCreateNewVisitNorChangeAttribution()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to direct entry
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not be updated, as url added to site urls
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to direct entry
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_DIRECT_ENTRY), $conversions[0]);
    }

    /**
     * Test Case 6:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * not using referrer attribution cookies
     * external (payment) provider added as site url
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithPaymentProviderAddedAsSiteUrlWillNotCreateNewVisitNorChangeAttribution()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to search engine
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 7:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * not using referrer attribution cookies
     * external (payment) provider added as site url
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     * 3. Ecommerce conversion
     *    --> conversion attributed to direct entry
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithNewVisitWhenWebsiteReferrerChangesAndPaymentProviderAddedAsSiteUrlWillNotCreateNewVisitNorChangeAttribution()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to direct entry
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);


        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // Check conversion is attributed to direct entry
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_DIRECT_ENTRY), $conversions[0]);
    }

    /**
     * Test Case 8:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * not using referrer attribution cookies
     * external (payment) provider added as site url
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithNewVisitWhenWebsiteReferrerChangesAndPaymentProviderAddedAsSiteUrlWillNotCreateNewVisitNorChangeAttribution()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine referrer
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to search engine
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 9:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * with referrer attribution cookie containing a search engine from a previous visit
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     * 2. Return from external (payment) provider
     *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
     *    --> visit now attributed to external (payment) provider
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine (due to attribution cookie)
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithReferrerAttributionCookieFromPreviousVisitWillNotCreateNewVisitButChangeAttributionOfVisitAndAttributeConversionWithCookieReferrer()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        $tracker->setUrl('https://matomo.org/');
        // send attribution data from last visit
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        // attribution cookie will stay the same (due to payment provider added to setDomains)
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer is updated to payment provider as url not added to site urls
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_WEBSITE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to search engine (due to attribution cookie)
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 10:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * with referrer attribution cookie placed when returning from payment provider (e.g. not in setDomains)
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     *    --> attribution cookie will be set to search engine
     * 2. Return from external (payment) provider
     *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
     *    --> visit attribution will not be changed
     *    --> attribution cookie will be updated to external (payment) provider (as not in setDomains)
     * 3. Ecommerce conversion
     *    --> conversion will be attributed to external (payment) provider
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithReferrerAttributionCookieUpdatedOnReturnWillNotCreateNewVisitNorChangeAttributionOfVisitButAttributeConversionToPaymentProvider()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        // referrer attribution cookie would be set to search engine url
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine referrer
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        // referrer attribution cookie would be updated to payment provider
        $tracker->setCustomTrackingParameter('_ref', 'https://payment.provider.info/success');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://payment.provider.info/success');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // conversion is attributed to payment provider in this case
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_WEBSITE), $conversions[0]);
    }

    /**
     * Test Case 11:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * with referrer attribution cookie containing a search engine from a previous visit
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     *    --> attribution cookie from previous visit remains
     * 2. Return from external (payment) provider
     *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
     *    --> the new visit will be attributed to external (payment) provider
     *    --> attribution cookie will remain from previous visit (due to setDomains)
     * 3. Ecommerce conversion
     *    --> conversion (of second visit) attributed to search engine (due to attribution cookie)
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithNewVisitWhenWebsiteReferrerChangesWithReferrerAttributionCookieFromPreviousVisitNotUpdatedOnReturnWillCreateNewVisitAttributedToPaymentProviderButWillAttributeConversionToCookieReferrer()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        // send attribution data from last visit
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to direct entry
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);


        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // a new visit will be created due to changed referrer and `create_new_visit_when_website_referrer_changes = 1`
        $visits = $this->getVisitReferrers();

        self::assertEquals(2, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);
        self::assertEquals($this->buildVisit(2, $idSite, 1, Common::REFERRER_TYPE_WEBSITE), $visits[1]);


        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // Check conversion is attributed to search engine (due to attribution cookie)
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('2', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 12:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * with referrer attribution cookie placed on entry from search engine
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     *    --> attribution cookie for search engine is placed
     * 2. Return from external (payment) provider
     *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
     *    --> the new visit will be attributed to external (payment) provider
     *    --> attribution cookie will remain from previous visit (due to setDomains)
     * 3. Ecommerce conversion
     *    --> conversion (of second visit) attributed to search engine (due to attribution cookie)
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithNewVisitWhenWebsiteReferrerChangesWithReferrerAttributionCookieNotUpdatedOnReturnWillCreateNewVisitAttributedToPaymentProviderButWillAttributeConversionToCookieReferrer()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        // referrer attribution cookie would be set to search engine url
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine referrer
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        // attribution cookie stays the same, as payment provider url is in setDomains
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // a new visit will be created due to changed referrer and `create_new_visit_when_website_referrer_changes = 1`
        $visits = $this->getVisitReferrers();

        self::assertEquals(2, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);
        self::assertEquals($this->buildVisit(2, $idSite, 1, Common::REFERRER_TYPE_WEBSITE), $visits[1]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // Check conversion is attributed to search engine (due to attribution cookie)
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('2', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 13:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * with referrer attribution cookie containing a search engine from previous visit
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     * external (payment) provider added as site url
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     *    --> attribution cookie from previous visit remains
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     *    --> attribution cookie will not be updated (as url in setDomains)
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine (due to attribution cookie)
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithPaymentProviderAddedAsSiteUrlAndReferrerAttributionCookieWillNotCreateNewVisitNorChangeAttributionButAttributeConversionWithCookieReferrer()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        // send attribution data from last visit
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to direct entry
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);


        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        // referrer attribution cookie doesn't change due to setDomains
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referrer should not be updated, as url added to site urls
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to search engine (due to attribution cookie)
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 14:
     *
     * config `create_new_visit_when_website_referrer_changes = 0`  (default)
     * with referrer attribution cookie containing being placed for search engine
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     * external (payment) provider added as site url
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     *    --> attribution cookie will be set to search engine
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     *    --> attribution cookie will not be updated (as url in setDomains)
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine (due to attribution cookie)
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithPaymentProviderAddedAsSiteUrlAndReferrerAttributionCookieWillNotCreateNewVisitNorChangeAttribution()
    {
        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        // attribution cookie will be placed
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine referrer
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        // attribution cookie will not be updated (as url in setDomains)
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed to search engine
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 15:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * with referrer attribution cookie containing a search engine from previous visit
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     * external (payment) provider added as site url
     *
     * 1. Direct entry
     *    --> visit attributed to direct entry
     *    --> attribution cookie from previous visit remains
     * 2. Return from external (payment) provider
     *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
     *    --> visit attribution will not be changed (due to url in site urls)
     *    --> attribution cookie will not be changed (as in setDomains)
     * 3. Ecommerce conversion
     *    --> conversion will be attributed to search engine (due to attribution cookie)
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToDirectEntryWithNewVisitWhenWebsiteReferrerChangesAndPaymentProviderAddedAsSiteUrlAndReferrerAttributionCookieWillNotCreateNewVisitNorChangeAttributionButAttributeConversionToCookieReferrer()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page (direct entry)
        $tracker->setUrlReferrer('');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine referrer
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_DIRECT_ENTRY), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // Check conversion is attributed to search engine (due to attribution cookie)
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    /**
     * Test Case 16:
     *
     * config `create_new_visit_when_website_referrer_changes = 1`
     * with referrer attribution cookie placed on entry from search engine
     * (assuming setDomains contains the external payment provider and the attribution cookie isn't replaced)
     * external (payment) provider added as site url
     *
     * 1. Entry from a search engine
     *    --> visit attributed to search engine
     *    --> attribution cookie for search engine is placed
     * 2. Return from external (payment) provider
     *    --> no new visit and no change in referrer as it's added to site urls
     *    --> attribution cookie will not be updated (as url in setDomains)
     * 3. Ecommerce conversion
     *    --> conversion attributed to search engine
     */
    public function testVisitorReturningFromPaymentProviderFirstAttributedToSearchEngineWithNewVisitWhenWebsiteReferrerChangesAndPaymentProviderAddedAsSiteUrlAndReferrerAttributionCookieWillNotCreateNewVisitNorChangeAttribution()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');
        SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, 'https://payment.provider.info');
        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        // Visitor enters page coming from search engine
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
        // attribution cookie will be placed
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed to search engine referrer
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 1, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        $tracker->setUrlReferrer('https://payment.provider.info/success');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // attributed referer should not change in this case
        $visits = $this->getVisitReferrers();

        self::assertEquals(1, count($visits));
        self::assertEquals($this->buildVisit(1, $idSite, 2, Common::REFERRER_TYPE_SEARCH_ENGINE), $visits[0]);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        $tracker->setCustomTrackingParameter('_ref', 'https://www.google.com/search?q=matomo');
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // Check conversion is attributed to search engine
        $conversions = $this->getConversionReferrers();

        self::assertEquals(1, count($conversions));
        self::assertEquals($this->buildConversion('1', $idSite, Common::REFERRER_TYPE_SEARCH_ENGINE), $conversions[0]);
    }

    private function buildVisit($idVisit, $idSite, $numActions, $referrerType)
    {
        switch ($referrerType) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                return  [
                    'idvisit' => $idVisit,
                    'idsite' => $idSite,
                    'visit_total_actions' => $numActions,
                    'referer_type' => Common::REFERRER_TYPE_SEARCH_ENGINE,
                    'referer_name' => 'Google',
                    'referer_keyword' => 'matomo',
                    'referer_url' => 'https://www.google.com/search?q=matomo'
                ];
            case Common::REFERRER_TYPE_WEBSITE:
                return  [
                    'idvisit' => $idVisit,
                    'idsite' => $idSite,
                    'visit_total_actions' => $numActions,
                    'referer_type' => Common::REFERRER_TYPE_WEBSITE,
                    'referer_name' => 'payment.provider.info',
                    'referer_keyword' => '',
                    'referer_url' => 'https://payment.provider.info/success'
                ];
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
            default:
                return  [
                    'idvisit' => $idVisit,
                    'idsite' => $idSite,
                    'visit_total_actions' => $numActions,
                    'referer_type' => Common::REFERRER_TYPE_DIRECT_ENTRY,
                    'referer_name' => '',
                    'referer_keyword' => '',
                    'referer_url' => ''
                ];
        }
    }

    private function buildConversion($idVisit, $idSite, $referrerType): array
    {
        switch ($referrerType) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                return  [
                    'idvisit' => $idVisit,
                    'idsite' => $idSite,
                    'referer_type' => Common::REFERRER_TYPE_SEARCH_ENGINE,
                    'referer_name' => 'Google',
                    'referer_keyword' => 'matomo',
                ];
            case Common::REFERRER_TYPE_WEBSITE:
                return  [
                    'idvisit' => $idVisit,
                    'idsite' => $idSite,
                    'referer_type' => Common::REFERRER_TYPE_WEBSITE,
                    'referer_name' => 'payment.provider.info',
                    'referer_keyword' => '',
                ];
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
            default:
                return  [
                    'idvisit' => $idVisit,
                    'idsite' => $idSite,
                    'referer_type' => Common::REFERRER_TYPE_DIRECT_ENTRY,
                    'referer_name' => '',
                    'referer_keyword' => '',
                ];
        }
    }

    private function getVisitReferrers()
    {
        return Db::fetchAll('SELECT idvisit, idsite, visit_total_actions, referer_type, referer_name, referer_keyword, referer_url FROM ' . Common::prefixTable('log_visit'));
    }

    private function getConversionReferrers()
    {
        return Db::fetchAll('SELECT idvisit, idsite, referer_type, referer_name, referer_keyword FROM ' . Common::prefixTable('log_conversion'));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
