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
        array $secondReferrer,
        ?array $referrerAttributionCookieValuesAfterReturn,
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
        $this->assertVisitReferrers([$this->buildVisit(1, 1, $initialReferrer)]);

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

        $shouldCreateNewVisit = false;
        $shouldUpdateReferrer = false;

        if (
            $createNewVisitWhenWebsiteReferrerChanges === true
            && $secondReferrer['referrerType'] === Common::REFERRER_TYPE_WEBSITE
            && $addSecondReferrerAsSiteUrl === false
        ) {
            $shouldCreateNewVisit = true;
        }

        if (
            $initialReferrer['referrerType'] === Common::REFERRER_TYPE_DIRECT_ENTRY
            && $addSecondReferrerAsSiteUrl === false
        ) {
            $shouldUpdateReferrer = true;
        }

        // check visits and referrers are attributed correctly
        if ($shouldCreateNewVisit) {
            $this->assertVisitReferrers([
                                            $this->buildVisit(1, 1, $initialReferrer),
                                            $this->buildVisit(2, 1, $secondReferrer),
                                        ]);
        } elseif ($shouldUpdateReferrer) {
            $this->assertVisitReferrers([$this->buildVisit(1, 2, $secondReferrer)]);
        } else {
            $this->assertVisitReferrers([$this->buildVisit(1, 2, $initialReferrer)]);
        }

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed correctly
        $conversionReferrer = $initialReferrerAttributionCookieValues ?? $initialReferrer;

        if ($referrerAttributionCookieValuesAfterReturn) {
            $conversionReferrer = $referrerAttributionCookieValuesAfterReturn;
        } elseif ($shouldCreateNewVisit || $shouldUpdateReferrer) {
            $conversionReferrer = $secondReferrer;
        }

        if ($conversionReferrer === $secondReferrer && $addSecondReferrerAsSiteUrl) {
            $conversionReferrer = $initialReferrer;
        }

        $this->assertConversionReferrers([$this->buildConversion($shouldCreateNewVisit ? 2 : 1, $conversionReferrer)]);
    }

    public function getReferrerAttributionUsingLastReferrerTestCases(): iterable
    {
        $possibleFirstReferrers = [
            self::$directEntryReferrer,
            self::$websiteReferrer,
            self::$searchEngineReferrer,
            self::$socialNetworkReferrer,
            //self::$campaignReferrer,
        ];

        $possibleSecondReferrers = [
            self::$directEntryReferrer,
            self::$externalServiceReferrer,
            self::$searchEngineReferrer2,
            self::$socialNetworkReferrer2,
            //self::$campaignReferrer2,
        ];

        $dataSet = 1;

        foreach ([false, true] as $keepReferrerAttributionCookieOnChange) {
            foreach ([false, true] as $useReferrerAttributionCookie) {
                foreach ([false, true] as $addSecondReferrerAsSiteUrl) {
                    foreach ([false, true] as $createNewVisitWhenWebsiteReferrerChanges) {
                        foreach ($possibleFirstReferrers as $firstReferrer) {
                            foreach ($possibleSecondReferrers as $secondReferrer) {
                                if (
                                    false === $useReferrerAttributionCookie
                                    && true === $keepReferrerAttributionCookieOnChange
                                ) {
                                    // skip tests (updating attribution cookie doesn't make sense if they are not used)
                                    continue;
                                }

                                if (
                                    Common::REFERRER_TYPE_DIRECT_ENTRY === $secondReferrer['referrerType']
                                    && (
                                        true === $addSecondReferrerAsSiteUrl
                                        || true === $keepReferrerAttributionCookieOnChange
                                    )
                                ) {
                                    // skip tests, adding host of direct entry has no effect
                                    continue;
                                }

                                $initialReferrerAttributionCookieValues = $referrerAttributionCookieValuesAfterReturn = null;

                                if (true === $useReferrerAttributionCookie) {
                                    $initialReferrerAttributionCookieValues = $firstReferrer;

                                    // when first referrer is direct, but we are using attribution cookies we use an attribution cookie set from previous visit
                                    if (Common::REFERRER_TYPE_DIRECT_ENTRY === $firstReferrer['referrerType']) {
                                        $initialReferrerAttributionCookieValues = self::$searchEngineReferrer;
                                    }

                                    // keep attribution cookie values from first visit by default
                                    $referrerAttributionCookieValuesAfterReturn = $initialReferrerAttributionCookieValues;

                                    if (!$keepReferrerAttributionCookieOnChange) {
                                        $referrerAttributionCookieValuesAfterReturn = $secondReferrer;
                                    }
                                }

                                yield "#$dataSet: createNewVisitWhenWebsiteReferrerChanges: " . (int) $createNewVisitWhenWebsiteReferrerChanges . " | " .
                                    "addSecondReferrerAsSiteUrl: " . (int) $addSecondReferrerAsSiteUrl . " | " .
                                    "useReferrerAttributionCookie: " . (int) $useReferrerAttributionCookie . " | " .
                                    "keepReferrerAttributionCookieOnChange: " . (int) $keepReferrerAttributionCookieOnChange . " | " .
                                    "firstReferrer: {$firstReferrer['referrerType']} | " .
                                    "secondReferrer: {$secondReferrer['referrerType']} "
                                => [
                                    $firstReferrer,
                                    $initialReferrerAttributionCookieValues,
                                    $secondReferrer,
                                    $referrerAttributionCookieValuesAfterReturn,
                                    $createNewVisitWhenWebsiteReferrerChanges,
                                    $addSecondReferrerAsSiteUrl
                                ];

                                $dataSet++;
                            }
                        }
                    }
                }
            }
        }
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
