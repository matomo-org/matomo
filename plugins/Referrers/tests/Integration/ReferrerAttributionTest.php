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
    public static $directEntryReferrer = [
        'siteUrl' => '',
        'referrerUrl' => '',
        'referrerName' => '',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_DIRECT_ENTRY,
        'attributionCookieValues' => [],
    ];

    public static $externalServiceReferrer = [
        'siteUrl' => 'https://payment.provider.info/',
        'referrerUrl' => 'https://payment.provider.info/success',
        'referrerName' => 'payment.provider.info',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_WEBSITE,
        'attributionCookieValues' => ['_ref' => 'https://payment.provider.info/success'],
    ];

    public static $websiteReferrer = [
        'siteUrl' => 'https://de.wikipedia.org/',
        'referrerUrl' => 'https://de.wikipedia.org/wiki/Matomo',
        'referrerName' => 'de.wikipedia.org',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_WEBSITE,
        'attributionCookieValues' => ['_ref' => 'https://de.wikipedia.org/wiki/Matomo'],
    ];

    public static $searchEngineReferrer = [
        'siteUrl' => 'https://www.google.com/',
        'referrerUrl' => 'https://www.google.com/search?q=matomo',
        'referrerName' => 'Google',
        'referrerKeyword' => 'matomo',
        'referrerType' => Common::REFERRER_TYPE_SEARCH_ENGINE,
        'attributionCookieValues' => ['_ref' => 'https://www.google.com/search?q=matomo'],
    ];

    public static $searchEngineReferrer2 = [
        'siteUrl' => 'https://www.bing.com/',
        'referrerUrl' => 'https://www.bing.com/search?q=matomo',
        'referrerName' => 'Bing',
        'referrerKeyword' => 'matomo',
        'referrerType' => Common::REFERRER_TYPE_SEARCH_ENGINE,
        'attributionCookieValues' => ['_ref' => 'https://www.bing.com/search?q=matomo'],
    ];

    public static $socialNetworkReferrer = [
        'siteUrl' => 'https://twitter.com/',
        'referrerUrl' => 'https://twitter.com/matomo_org',
        'referrerName' => 'Twitter',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_SOCIAL_NETWORK,
        'attributionCookieValues' => ['_ref' => 'https://twitter.com/matomo_org'],
    ];

    public static $socialNetworkReferrer2 = [
        'siteUrl' => 'https://l.instagram.com/',
        'referrerUrl' => 'https://l.instagram.com/?u=https%3A%2F%2Fexample.com%2Fexample.com',
        'referrerName' => 'Instagram',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_SOCIAL_NETWORK,
        'attributionCookieValues' => ['_ref' => 'https://l.instagram.com/?u=https%3A%2F%2Fexample.com%2Fexample.com'],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 0); // set to default
        $env->save();
    }

    /**
     * @dataProvider getReferrerAttributionUsingLastReferrerTestCases
     */
    public function testReferrerAttributionUsingLastReferrer(
        array $initialReferrer,
        ?array $initialReferrerAttributionCookieValues,
        array $expectedVisitsAfterFirstAction,
        array $secondReferrer,
        ?array $referrerAttributionCookieValuesAfterReturn,
        array $expectedVisitsAfterServiceReturn,
        array $expectedConversions,
        bool $createNewVisitWhenWebsiteReferrerChanges,
        bool $addSecondReferrerAsSiteUrl
    ) {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', (int) $createNewVisitWhenWebsiteReferrerChanges);
        $env->save();

        $idSite = Fixture::createWebsite('2020-01-01 02:00:00', true, 'test', 'https://matomo.org/');

        if ($addSecondReferrerAsSiteUrl) {
            SitesManagerAPI::getInstance()->addSiteAliasUrls($idSite, $secondReferrer['siteUrl']);
        }

        $tracker = Fixture::getTracker($idSite, '2020-01-01 05:00:00');

        $referrerAttributionCookieValues = $initialReferrerAttributionCookieValues
            ? $initialReferrerAttributionCookieValues['attributionCookieValues']
            : [];

        // Visitor enters page
        $tracker->setUrlReferrer($initialReferrer['referrerUrl']);
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        $tracker->setUrl('https://matomo.org/');
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed correctly
        $this->assertVisitReferrers($expectedVisitsAfterFirstAction);

        if ($referrerAttributionCookieValuesAfterReturn) {
            $referrerAttributionCookieValues = $referrerAttributionCookieValuesAfterReturn
                ? $referrerAttributionCookieValuesAfterReturn['attributionCookieValues']
                : [];
        }

        // Now the visitor returns from a service
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        $tracker->setUrlReferrer($secondReferrer['referrerUrl']);
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

    public function getReferrerAttributionUsingLastReferrerTestCases()
    {
        return [
            /**
             * Test Case 1:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Another direct entry
             *    --> visit still attributed to direct entry
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 2:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit now attributed to external service
             * 3. Ecommerce conversion
             *   --> conversion attributed to external service
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$externalServiceReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 3:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit now attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 4:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit now attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 5:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry is direct
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 6:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 7:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 8:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],


            /**
             * Test Case 9:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry is direct
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 10:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 11:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to first search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to first search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 12:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 13:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry is direct
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 14:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 15:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from a search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 16:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> visit still attributed to first social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to first social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],

            /**
             * Test Case 17:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Another direct entry
             *    --> visit still attributed to direct entry
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 18:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$directEntryReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer)
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 19:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from Search Engine
             *    --> visit now attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 20:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> visit now attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 21:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry is direct
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 22:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$websiteReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer)
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 23:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from Search Engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> the visit is still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 24:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> the visit is still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 25:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry is direct
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 26:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$searchEngineReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer),
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 27:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to website
             * 2. Second entry from another search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> the visit is still attributed to first search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to first search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 28:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from a social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> the visit is still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 29:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry is direct
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 30:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$socialNetworkReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer),
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 31:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> the visit is still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 32:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * not using referrer attribution cookies
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> the visit is still attributed to first social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to first social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 33:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 34:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 35:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 36:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 37:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 38:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 39:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 40:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 41:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 42:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 43:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 44:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 45:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 46:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 47:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 48:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 49:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 50:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 51:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 52:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 53:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 54:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 55:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 56:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * not using referrer attribution cookies
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit and no change in referrer as it's added to site urls
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = null,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = null,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 57:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Another direct entry
             *    --> visit still attributed to direct entry
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 58:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> referrer attribution cookie will be updated to external service as domain not in setDomains
             *    --> visit now attributed to external service
             * 3. Ecommerce conversion
             *   --> conversion attributed to external service
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$externalServiceReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 59:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from another search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> referrer attribution cookie will be updated to second search engine as domain not in setDomains
             *    --> visit now attributed to second search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to second search engine
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer2)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer2)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 60:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> referrer attribution cookie will be updated to social network as domain not in setDomains
             *    --> visit now attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 61:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry is direct
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 62:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to external service
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to external service (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 63:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to search engine
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 64:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to social network
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 65:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry is direct
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 66:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to external service
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to external service (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 67:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to second search engine
             *    --> visit still attributed to first search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to second search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer2)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 68:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to social network
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 69:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry is direct
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 70:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to external service
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to external service
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 71:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from a search engine
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to search engine
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 72:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> attribution cookie will be updated to second social network
             *    --> visit still attributed to first social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to second social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer2)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],

            /**
             * Test Case 73:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Another direct entry
             *    --> visit still attributed to direct entry
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 74:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> attribution cookie will be updated to external service
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$directEntryReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer)
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 75:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from another Search Engine
             *    --> visit now attributed to second search engine
             *    --> attribution cookie will be updated to second search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to second search engine
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer2)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer2)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 76:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> visit now attributed to social network
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 77:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry is direct
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 78:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> attribution cookie will be updated to external service
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$websiteReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer)
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 79:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from Search Engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> attribution cookie will be updated to search engine
             *    --> the visit is still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 80:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> attribution cookie will be updated to social network
             *    --> the visit is still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 81:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry is direct
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 82:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> attribution cookie will be updated to external service
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$searchEngineReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer),
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 83:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to website
             * 2. Second entry from another search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> attribution cookie will be updated to second search engine
             *    --> the visit is still attributed to first search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to second search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer2)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 84:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from a search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> attribution cookie will be updated to search engine
             *    --> the visit is still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 85:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry is direct
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$directEntryReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 86:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> attribution cookie will be updated to external service
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$socialNetworkReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer),
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 87:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> attribution cookie will be updated to search engine
             *    --> the visit is still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 88:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> attribution cookie will be updated to second social network
             *    --> the visit is still attributed to first social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to second social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer2)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 89:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 90:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to second search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 91:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 92:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 93:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 94:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 95:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 96:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to second search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 97:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 98:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 99:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 100:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to second social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 101:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 102:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 103:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to direct entry
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$directEntryReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 104:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 105
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 106:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to website
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 107:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 108:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to second search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 109:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 110:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to external service
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 111
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 112:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> attribution cookie will be updated to second social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) social network
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer2,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 113:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit now attributed to external service
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$externalServiceReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 114:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from another search engine
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit now attributed to second search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to second search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer2)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 115:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit now attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 116:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 117:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 118:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to website
             * 3. Ecommerce conversion
             *   --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 119:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit will be created due to `create_new_visit_when_website_referrer_changes = 0`
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 120:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to first search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to first search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 121:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie updated on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to search engine
             * 3. Ecommerce conversion
             *   --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 122:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 123:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from a search engine
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 124:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> visit still attributed to first social network
             * 3. Ecommerce conversion
             *   --> conversion attributed to first social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 125:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$directEntryReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer)
                ],
                $expectedConversions = [$this->buildConversion(2, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 126:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from another Search Engine
             *    --> visit now attributed to second search engine
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to first search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer2)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 127:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> visit now attributed to social network
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 128:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to external service (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$externalServiceReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$websiteReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer)
                ],
                $expectedConversions = [$this->buildConversion(2, self::$externalServiceReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 129:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from Search Engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the visit is still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 130:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the visit is still attributed to website
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 131:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$searchEngineReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer),
                ],
                $expectedConversions = [$this->buildConversion(2, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 132:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a search engine
             *    --> visit attributed to website
             * 2. Second entry from another search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the visit is still attributed to first search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to first search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 133:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a search engine
             *    --> visit attributed to search engine
             * 2. Second entry from a search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the visit is still attributed to search engine
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribtuion cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 134:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> new visit will be created due to new referrer and `create_new_visit_when_website_referrer_changes = 1`
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the new visit will be attributed to external service
             * 3. Ecommerce conversion
             *    --> conversion (of second visit) attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [
                    $this->buildVisit(1, 1, self::$socialNetworkReferrer),
                    $this->buildVisit(2, 1, self::$externalServiceReferrer),
                ],
                $expectedConversions = [$this->buildConversion(2, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 135:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the visit is still attributed to social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 136:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * no additional site urls
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from a social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit will be created as new referrer is no website referrer
             *    --> referrer attribution cookie will not be updated due to setDomains
             *    --> the visit is still attributed to first social network
             * 3. Ecommerce conversion
             *    --> conversion attributed to first social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = false
            ],
            /**
             * Test Case 137:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 138:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to first search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 139:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 140:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 141:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 142:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 143:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 144:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) search engine (due to attribtuion cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 145:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 146:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 147:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 148:
             *
             * config `create_new_visit_when_website_referrer_changes = 0`  (default)
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = false,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 149:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 150:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to first search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 151:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * with referrer attribution cookie containing a search engine from a previous visit
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Direct entry
             *    --> visit attributed to direct entry
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$directEntryReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$directEntryReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$directEntryReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 152:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 153:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 154:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from website
             *    --> visit attributed to website
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to website (due to attribution cookie)
             */
            [
                $initialReferrer = self::$websiteReferrer,
                $initialReferrerAttributionCookieValues = self::$websiteReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$websiteReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$websiteReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$websiteReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$websiteReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 155:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 156:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from another search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$searchEngineReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 157:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from search engine
             *    --> visit attributed to search engine
             * 2. Second entry from social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to search engine (due to attribution cookie)
             */
            [
                $initialReferrer = self::$searchEngineReferrer,
                $initialReferrerAttributionCookieValues = self::$searchEngineReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$searchEngineReferrer)],
                $secondReferrer = self::$socialNetworkReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$searchEngineReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$searchEngineReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$searchEngineReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 158:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * external service added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Return from external service
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$externalServiceReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 159:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * search engine added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from search engine
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$searchEngineReferrer,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
            /**
             * Test Case 160:
             *
             * config `create_new_visit_when_website_referrer_changes = 1`
             * social network added as site url
             * referrer attribution cookie update on changing referrer
             * (assuming setDomains contains the second referrers host and the attribution cookie isn't replaced)
             *
             * 1. Entry from social network
             *    --> visit attributed to social network
             * 2. Second entry from another social network
             *    --> no new visit and no change in referrer as it's added to site urls
             *    --> referrer attribution cookie will not be updated due to setDomains
             * 3. Ecommerce conversion
             *    --> conversion attributed to (first) social network (due to attribution cookie)
             */
            [
                $initialReferrer = self::$socialNetworkReferrer,
                $initialReferrerAttributionCookieValues = self::$socialNetworkReferrer,
                $expectedVisitsAfterFirstAction = [$this->buildVisit(1, 1, self::$socialNetworkReferrer)],
                $secondReferrer = self::$socialNetworkReferrer2,
                $referrerAttributionCookieValuesAfterReturn = self::$socialNetworkReferrer,
                $expectedVisitsAfterServiceReturn = [$this->buildVisit(1, 2, self::$socialNetworkReferrer)],
                $expectedConversions = [$this->buildConversion(1, self::$socialNetworkReferrer)],
                $createNewVisitWhenWebsiteReferrerChanges = true,
                $addSecondReferrerAsSiteUrl = true
            ],
        ];
    }

    private function setReferrerAttributionCookieValuesToTracker(\MatomoTracker $tracker, array $cookieValues): void
    {
        foreach ($cookieValues as $key => $value) {
            $tracker->setCustomTrackingParameter($key, $value);
        }
    }

    private function buildVisit($idVisit, $numActions, $referrer): array
    {
        return  [
            'idvisit' => $idVisit,
            'visit_total_actions' => $numActions,
            'referer_type' => $referrer['referrerType'],
            'referer_name' => $referrer['referrerName'],
            'referer_keyword' => $referrer['referrerKeyword'],
            'referer_url' => $referrer['referrerUrl'],
        ];
    }

    private function buildConversion($idVisit, $referrer): array
    {
        return  [
            'idvisit' => $idVisit,
            'referer_type' => $referrer['referrerType'],
            'referer_name' => $referrer['referrerName'],
            'referer_keyword' => $referrer['referrerKeyword'],
        ];
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
