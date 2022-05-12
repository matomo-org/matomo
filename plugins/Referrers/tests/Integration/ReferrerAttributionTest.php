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
    public static $externalService = [
        'siteUrl' => 'https://payment.provider.info/',
        'referrerUrl' => 'https://payment.provider.info/success',
        'referrerName' => 'payment.provider.info'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 0); // set to default
        $env->save();
    }

    /**
     * @param       $initialReferrerUrl
     * @param       $initialReferrerAttributionCookieValues
     * @param       $expectedVisitsAfterFirstAction
     * @param       $referrerAttributionCookieValuesAfterReturn
     * @param       $expectedVisitsAfterServiceReturn
     * @param       $expectedConversions
     * @param       $createNewVisitWhenWebsiteReferrerChanges
     * @param       $addSiteUrls
     * @throws \Exception
     *
     * @dataProvider getVisitorReturningFromPaymentAttributedCorrectlyTestCases
     */
    public function testVisitorReturningFromPaymentAttributedCorrectly(
        $initialReferrerUrl,
        $initialReferrerAttributionCookieValues,
        $expectedVisitsAfterFirstAction,
        $referrerAttributionCookieValuesAfterReturn,
        $expectedVisitsAfterServiceReturn,
        $expectedConversions,
        $createNewVisitWhenWebsiteReferrerChanges = false,
        $addSiteUrls = false
    ) {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', (int) $createNewVisitWhenWebsiteReferrerChanges);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');

        if (is_array($addSiteUrls)) {
            SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, $addSiteUrls);
        }

        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        $referrerAttributionCookieValues = $initialReferrerAttributionCookieValues;

        // Visitor enters page
        $tracker->setUrlReferrer($initialReferrerUrl);
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed correctly
        $this->assertVisitReferrers($expectedVisitsAfterFirstAction);

        if ($referrerAttributionCookieValuesAfterReturn) {
            $referrerAttributionCookieValues = $referrerAttributionCookieValuesAfterReturn;
        }

        // Now the visitor returns from a payment provider
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        $tracker->setUrlReferrer(self::$externalService['referrerUrl']);
        $tracker->setUrl('https://matomo.org/order/payed?paymentid=1337');
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        // check visits and referrers are attributed correctly
        $this->assertVisitReferrers($expectedVisitsAfterServiceReturn);

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed correctly
        $this->assertConversionReferrers($expectedConversions);
    }

    public function getVisitorReturningFromPaymentAttributedCorrectlyTestCases()
    {
        return [
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_WEBSITE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_WEBSITE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY),
                    $this->buildVisit(2, 1, Common::REFERRER_TYPE_WEBSITE)
                ],
                $expectedConversions = [$this->buildConversion(2, Common::REFERRER_TYPE_WEBSITE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE),
                    $this->buildVisit(2, 1, Common::REFERRER_TYPE_WEBSITE),
                ],
                $expectedConversions = [$this->buildConversion(2, Common::REFERRER_TYPE_WEBSITE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = [],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = [],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_WEBSITE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://payment.provider.info/success'],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_WEBSITE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY),
                    $this->buildVisit(2, 1, Common::REFERRER_TYPE_WEBSITE)
                ],
                $expectedConversions = [$this->buildConversion(2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE),
                    $this->buildVisit(2, 1, Common::REFERRER_TYPE_WEBSITE)
                ],
                $expectedConversions = [$this->buildConversion(2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = false
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = '',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_DIRECT_ENTRY)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
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
            [
                $initialReferrerUrl = 'https://www.google.com/search?q=matomo',
                $initialReferrerAttributionCookieValues = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $referrerAttributionCookieValuesAfterReturn = ['_ref' => 'https://www.google.com/search?q=matomo'],
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $expectedConversions = [$this->buildConversion(1, Common::REFERRER_TYPE_SEARCH_ENGINE)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSiteUrls = [self::$externalService['siteUrl']]
            ],
        ];
    }

    private function setReferrerAttributionCookieValuesToTracker(\MatomoTracker $tracker, array $cookieValues): void
    {
        foreach ($cookieValues as $key => $value) {
            $tracker->setCustomTrackingParameter($key, $value);
        }
    }

    private function buildVisit($idVisit, $numActions, $referrerType): array
    {
        switch ($referrerType) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                return  [
                    'idvisit' => $idVisit,
                    'visit_total_actions' => $numActions,
                    'referer_type' => Common::REFERRER_TYPE_SEARCH_ENGINE,
                    'referer_name' => 'Google',
                    'referer_keyword' => 'matomo',
                    'referer_url' => 'https://www.google.com/search?q=matomo'
                ];
            case Common::REFERRER_TYPE_WEBSITE:
                return  [
                    'idvisit' => $idVisit,
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
                    'visit_total_actions' => $numActions,
                    'referer_type' => Common::REFERRER_TYPE_DIRECT_ENTRY,
                    'referer_name' => '',
                    'referer_keyword' => '',
                    'referer_url' => ''
                ];
        }
    }

    private function buildConversion($idVisit, $referrerType): array
    {
        switch ($referrerType) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                return  [
                    'idvisit' => $idVisit,
                    'referer_type' => Common::REFERRER_TYPE_SEARCH_ENGINE,
                    'referer_name' => 'Google',
                    'referer_keyword' => 'matomo',
                ];
            case Common::REFERRER_TYPE_WEBSITE:
                return  [
                    'idvisit' => $idVisit,
                    'referer_type' => Common::REFERRER_TYPE_WEBSITE,
                    'referer_name' => 'payment.provider.info',
                    'referer_keyword' => '',
                ];
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
            default:
                return  [
                    'idvisit' => $idVisit,
                    'referer_type' => Common::REFERRER_TYPE_DIRECT_ENTRY,
                    'referer_name' => '',
                    'referer_keyword' => '',
                ];
        }
    }

    private function assertVisitReferrers($expectedVisits): void
    {
        self::assertEquals($expectedVisits, $this->getVisitReferrers());
    }

    private function assertConversionReferrers($expectedConversions): void
    {
        self::assertEquals($expectedConversions, $this->getConversionReferrers());
    }

    private function getVisitReferrers()
    {
        return Db::fetchAll('SELECT idvisit, visit_total_actions, referer_type, referer_name, referer_keyword, referer_url FROM ' . Common::prefixTable('log_visit'));
    }

    private function getConversionReferrers()
    {
        return Db::fetchAll('SELECT idvisit, referer_type, referer_name, referer_keyword FROM ' . Common::prefixTable('log_conversion'));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
