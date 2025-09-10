<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

use MatomoTracker;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;

class FewVisitsAnonymizedFixture extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $idSite2 = 2;

    public function setUp(): void
    {
        Option::set(PrivacyManager::OPTION_USERID_SALT, 'simpleuseridsalt1');
        Cache::clearCacheGeneral();

        $this->setUpWebsite();
        $this->trackAnonymizedUserId();
        $this->trackAnonymizedOrderId();
        $this->trackAnonymizedReferrerExcludeAllSearch();
        $this->trackAnonymizedReferrerExcludeAllWebsite();
        $this->trackAnonymizedReferrerExcludePathWebsite();
        $this->trackAnonymizedReferrerExcludeQuerySocial();
        $this->trackAnonymizedReferrerExcludeAllSocial();
        $this->trackAnonymizedReferrerExcludeAllCampaign();
        $this->trackAnonymizedIp();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function getPrivacyConfig(int $idSite = null)
    {
        return new Config($idSite);
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
        if (!self::siteCreated($this->idSite2)) {
            $idSite2 = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite2, $idSite2);
        }
    }

    /**
     * Returns a pre-configured MatomoTracker
     *
     * @param int $idSite
     * @param string $dateTime
     * @param string $urlPath
     * @param string $ip
     * @return MatomoTracker
     * @throws \Exception
     */
    private static function prepareTracker(int $idSite, string $dateTime, string $urlPath = '', string $ip = ''): MatomoTracker
    {
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/' . $urlPath);

        if ('' !== $ip) {
            $t->setIp($ip);
        }

        return $t;
    }

    protected function trackAnonymizedUserId()
    {
        $this->getPrivacyConfig()->anonymizeUserId = true;

        $t = self::prepareTracker($this->idSite, $this->dateTime);
        $t->setUserId('foobar');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $this->getPrivacyConfig()->anonymizeUserId = false;
        $this->getPrivacyConfig($this->idSite2)->anonymizeUserId = true;

        $t = self::prepareTracker($this->idSite2, $this->dateTime);
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
    }

    protected function trackAnonymizedOrderId()
    {
        $this->getPrivacyConfig()->anonymizeOrderId = true;

        $t = self::prepareTracker($this->idSite, $this->dateTime, 'myorder', '56.11.55.73');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
        $t->doTrackEcommerceOrder('myorderid', 10, 7, 2, 1, 0);

        $this->getPrivacyConfig()->anonymizeOrderId = false;
        $this->getPrivacyConfig($this->idSite2)->anonymizeOrderId = true;

        $t = self::prepareTracker($this->idSite2, $this->dateTime, 'myorder2', '222.22.55.73');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
        $t->doTrackEcommerceOrder('myorderid2', 10, 7, 2, 1, 0);
    }

    protected function trackAnonymizedReferrerExcludeAllWebsite()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker($this->idSite, $this->dateTime, 'exclude_all', '56.11.55.74');
        $t->setUrlReferrer('https://www.foo.com/bar/?baz=exclude_all');
        self::checkResponse($t->doTrackPageView('Exclude all referrer website'));

        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $this->getPrivacyConfig($this->idSite2)->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker($this->idSite2, $this->dateTime, 'exclude_all', '222.22.55.74');
        $t->setUrlReferrer('https://www.foo.com/bar/?baz=exclude_all');
        self::checkResponse($t->doTrackPageView('Exclude all referrer website'));
    }

    protected function trackAnonymizedReferrerExcludePathWebsite()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_PATH;

        $t = self::prepareTracker($this->idSite, $this->dateTime, 'exclude_path_website', '56.11.55.75');
        $t->setUrlReferrer('https://www.foo.com/bar/?baz=exclude_path_website');
        self::checkResponse($t->doTrackPageView('Exclude path website'));

        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $this->getPrivacyConfig($this->idSite2)->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_PATH;

        $t = self::prepareTracker($this->idSite2, $this->dateTime, 'exclude_path_website', '222.22.55.75');
        $t->setUrlReferrer('https://www.foo.com/bar/?baz=exclude_path_website');
        self::checkResponse($t->doTrackPageView('Exclude path website'));
    }

    protected function trackAnonymizedReferrerExcludeAllSearch()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker($this->idSite, $this->dateTime, 'exclude_all_search', '56.11.55.76');
        $t->setUrlReferrer('http://google.com/search?q=exclude_all_search');
        self::checkResponse($t->doTrackPageView('Exclude all search'));

        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $this->getPrivacyConfig($this->idSite2)->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker($this->idSite2, $this->dateTime, 'exclude_all_search', '222.22.55.76');
        $t->setUrlReferrer('http://google.com/search?q=exclude_all_search');
        self::checkResponse($t->doTrackPageView('Exclude all search'));
    }

    protected function trackAnonymizedReferrerExcludeQuerySocial()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_QUERY;

        $t = self::prepareTracker($this->idSite, $this->dateTime, 'exclude_query_social', '56.11.55.77');
        $t->setUrlReferrer('https://www.facebook.com/profile?id=exclude_query_social');
        self::checkResponse($t->doTrackPageView('Exclude query social'));

        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $this->getPrivacyConfig($this->idSite2)->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_QUERY;

        $t = self::prepareTracker($this->idSite2, $this->dateTime, 'exclude_query_social', '222.22.55.77');
        $t->setUrlReferrer('https://www.facebook.com/profile?id=exclude_query_social');
        self::checkResponse($t->doTrackPageView('Exclude query social'));
    }

    protected function trackAnonymizedReferrerExcludeAllSocial()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker($this->idSite, $this->dateTime, 'exclude_query_social', '56.11.55.78');
        $t->setUrlReferrer('https://www.facebook.com/profile?id=exclude_query_social');
        self::checkResponse($t->doTrackPageView('Exclude query social'));

        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $this->getPrivacyConfig($this->idSite2)->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker($this->idSite2, $this->dateTime, 'exclude_query_social', '222.22.55.78');
        $t->setUrlReferrer('https://www.facebook.com/profile?id=exclude_query_social');
        self::checkResponse($t->doTrackPageView('Exclude query social'));
    }

    protected function trackAnonymizedReferrerExcludeAllCampaign()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker(
            $this->idSite,
            $this->dateTime,
            'exclude_query_social?mtm_kwd=campaignkeyword&mtm_campaign=campaign',
            '56.11.55.78'
        );
        $t->setUrlReferrer('https://www.example.com/exclude_all_campaign');
        self::checkResponse($t->doTrackPageView('Exclude query social'));

        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $this->getPrivacyConfig($this->idSite2)->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::prepareTracker(
            $this->idSite2,
            $this->dateTime,
            'exclude_query_social?mtm_kwd=campaignkeyword&mtm_campaign=campaign',
            '222.22.55.78'
        );
        $t->setUrlReferrer('https://www.example.com/exclude_all_campaign');
        self::checkResponse($t->doTrackPageView('Exclude query social'));
    }

    protected function trackAnonymizedIp()
    {
        $pc = $this->getPrivacyConfig();
        $pc->ipAnonymizerEnabled = true;
        $pc->ipAddressMaskLength = 3;

        $t = self::prepareTracker($this->idSite, $this->dateTime);
        $t->setForceNewVisit();
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $pc->ipAnonymizerEnabled = false;

        $pc2 = $this->getPrivacyConfig($this->idSite2);
        $pc2->ipAnonymizerEnabled = true;
        $pc2->ipAddressMaskLength = 4;

        $t = self::prepareTracker($this->idSite2, $this->dateTime);
        $t->setForceNewVisit();
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $pc2->ipAnonymizerEnabled = false;
    }
}
