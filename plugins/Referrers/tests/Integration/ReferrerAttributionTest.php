<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public static $websiteReferrer = [
        'siteUrl' => 'https://de.wikipedia.org/',
        'referrerUrl' => 'https://de.wikipedia.org/wiki/Matomo',
        'referrerName' => 'de.wikipedia.org',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_WEBSITE,
        'attributionCookieValues' => ['_ref' => 'https://de.wikipedia.org/wiki/Matomo'],
    ];

    public static $websiteReferrer2 = [
        'siteUrl' => 'https://payment.provider.info/',
        'referrerUrl' => 'https://payment.provider.info/success',
        'referrerName' => 'payment.provider.info',
        'referrerKeyword' => '',
        'referrerType' => Common::REFERRER_TYPE_WEBSITE,
        'attributionCookieValues' => ['_ref' => 'https://payment.provider.info/success'],
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

    public static $campaignReferrer = [
        'siteUrl' => 'https://some.external.page/',
        'referrerUrl' => 'https://some.external.page/referrer',
        'campaignParameters' => 'pk_campaign=Campaign%20Name&pk_kwd=Campaign%20Keyword',
        'referrerName' => 'campaign name',
        'referrerKeyword' => 'campaign keyword',
        'referrerType' => Common::REFERRER_TYPE_CAMPAIGN,
        'attributionCookieValues' => [
            '_rcn' => 'Campaign Name',
            '_rck' => 'Campaign Keyword',
        ],
    ];

    public static $campaignReferrer2 = [
        'siteUrl' => 'https://some.other.page/',
        'referrerUrl' => 'https://some.other.page/path',
        'campaignParameters' => 'pk_campaign=Another%20Name&pk_kwd=Another%20Keyword',
        'referrerName' => 'another name',
        'referrerKeyword' => 'another keyword',
        'referrerType' => Common::REFERRER_TYPE_CAMPAIGN,
        'attributionCookieValues' => [
            '_rcn' => 'Another Name',
            '_rck' => 'Another Keyword',
        ],
    ];

    /**
     * @dataProvider getReferrerAttributionUsingLastReferrerTestCases
     */
    public function testReferrerAttributionUsingLastReferrer(
        array $initialReferrer,
        ?array $initialReferrerAttributionCookieValues,
        array $secondReferrer,
        ?array $referrerAttributionCookieValuesAfterReturn,
        bool $createNewVisitWhenWebsiteReferrerChanges,
        bool $addSecondReferrerAsSiteUrl,
        bool $createNewVisitWhenCampaignChanges
    ) {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', (int) $createNewVisitWhenWebsiteReferrerChanges);
        $env->overrideConfig('Tracker', 'create_new_visit_when_campaign_changes', (int) $createNewVisitWhenCampaignChanges);
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
        $url = 'https://matomo.org/';
        if (isset($initialReferrer['campaignParameters'])) {
            $url .= '?' . $initialReferrer['campaignParameters'];
        }
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('Home'));

        // check that the visit is attributed correctly
        $this->assertVisitReferrers([$this->buildVisit(1, 1, $initialReferrer)]);

        if ($referrerAttributionCookieValuesAfterReturn) {
            $referrerAttributionCookieValues = $referrerAttributionCookieValuesAfterReturn['attributionCookieValues'];
        }

        // Now the visitor returns from a service
        $tracker->setForceVisitDateTime('2020-01-01 05:04:38');
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        $tracker->setUrlReferrer($secondReferrer['referrerUrl']);
        $url = 'https://matomo.org/order/payed?paymentid=1337';
        if (isset($secondReferrer['campaignParameters'])) {
            $url .= '&' . $secondReferrer['campaignParameters'];
        }
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('Order payed'));

        $shouldCreateNewVisit = false;

        $visitReferrer = $initialReferrer;

        if (
            $secondReferrer['referrerType'] === Common::REFERRER_TYPE_CAMPAIGN
            && $createNewVisitWhenCampaignChanges === true
            && $initialReferrer['referrerType'] !== Common::REFERRER_TYPE_DIRECT_ENTRY
        ) {
            $visitReferrer = $secondReferrer;
            $shouldCreateNewVisit = true;
        } elseif (
            $secondReferrer['referrerType'] === Common::REFERRER_TYPE_WEBSITE
            && $createNewVisitWhenWebsiteReferrerChanges === true
            && $addSecondReferrerAsSiteUrl === false
        ) {
            $shouldCreateNewVisit = true;
            $visitReferrer = $secondReferrer;
        } elseif (
            $initialReferrer['referrerType'] === Common::REFERRER_TYPE_DIRECT_ENTRY
            && $addSecondReferrerAsSiteUrl === false
        ) {
            $visitReferrer = $secondReferrer;
        }

        // check visits and referrers are attributed correctly
        if ($shouldCreateNewVisit) {
            $this->assertVisitReferrers([
                                            $this->buildVisit(1, 1, $initialReferrer),
                                            $this->buildVisit(2, 1, $visitReferrer),
                                        ]);
        } else {
            $this->assertVisitReferrers([$this->buildVisit(1, 2, $visitReferrer)]);
        }

        // Track an ecommerce conversion
        $tracker->setForceVisitDateTime('2020-01-01 05:05:38');
        // attach referrer attribution cookie values if any
        $this->setReferrerAttributionCookieValuesToTracker($tracker, $referrerAttributionCookieValues);
        Fixture::checkResponse($tracker->doTrackEcommerceOrder('TestingOrder', 124.5));

        // check that conversion is attributed correctly
        if (
            $referrerAttributionCookieValuesAfterReturn !== null
            && $referrerAttributionCookieValuesAfterReturn['referrerType'] === Common::REFERRER_TYPE_CAMPAIGN
        ) {
            // if campaign was provided through cookie this will always be used
            $conversionReferrer = $referrerAttributionCookieValuesAfterReturn;
        } elseif ($visitReferrer['referrerType'] === Common::REFERRER_TYPE_CAMPAIGN) {
            // if campaign is referrer of current visit use this
            $conversionReferrer = $visitReferrer;
        } elseif (
            $referrerAttributionCookieValuesAfterReturn !== null
            && !(
                $referrerAttributionCookieValuesAfterReturn === $secondReferrer
                && $addSecondReferrerAsSiteUrl
            )
        ) {
            // The conversion will be attributed to the last value of the attribution cookie (if the host of this referrer wasn't added to the site urls)
            $conversionReferrer = $referrerAttributionCookieValuesAfterReturn;
        } else {
            $conversionReferrer = $visitReferrer;
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
            self::$campaignReferrer,
        ];

        $possibleSecondReferrers = [
            self::$directEntryReferrer,
            self::$websiteReferrer2,
            self::$searchEngineReferrer2,
            self::$socialNetworkReferrer2,
            self::$campaignReferrer2,
        ];

        $dataSet = 1;

        foreach ([false, true] as $createNewVisitWhenCampaignChanges) {
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
                                        (
                                            Common::REFERRER_TYPE_DIRECT_ENTRY === $secondReferrer['referrerType']
                                            || Common::REFERRER_TYPE_CAMPAIGN === $secondReferrer['referrerType']
                                        )
                                        && (
                                            true === $addSecondReferrerAsSiteUrl
                                            || true === $keepReferrerAttributionCookieOnChange
                                        )
                                    ) {
                                        // skip tests, adding host of direct entry or campaign has no effect
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

                                        if (
                                            !$keepReferrerAttributionCookieOnChange
                                            && Common::REFERRER_TYPE_DIRECT_ENTRY !== $secondReferrer['referrerType']
                                        ) {
                                            $referrerAttributionCookieValuesAfterReturn = $secondReferrer;
                                        }
                                    }

                                    yield "#$dataSet: createNewVisitWhenWebsiteReferrerChanges: " . (int)$createNewVisitWhenWebsiteReferrerChanges . " | " .
                                        "addSecondReferrerAsSiteUrl: " . (int)$addSecondReferrerAsSiteUrl . " | " .
                                        "useReferrerAttributionCookie: " . (int)$useReferrerAttributionCookie . " | " .
                                        "keepReferrerAttributionCookieOnChange: " . (int)$keepReferrerAttributionCookieOnChange . " | " .
                                        "createNewVisitWhenCampaignChanges: " . (int)$createNewVisitWhenCampaignChanges . " | " .
                                        "firstReferrer: {$firstReferrer['referrerType']} | " .
                                        "secondReferrer: {$secondReferrer['referrerType']} "
                                    => [
                                        $firstReferrer,
                                        $initialReferrerAttributionCookieValues,
                                        $secondReferrer,
                                        $referrerAttributionCookieValuesAfterReturn,
                                        $createNewVisitWhenWebsiteReferrerChanges,
                                        $addSecondReferrerAsSiteUrl,
                                        $createNewVisitWhenCampaignChanges
                                    ];

                                    $dataSet++;
                                }
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
