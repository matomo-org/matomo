<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

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
    }

    public function tearDown(): void
    {
        // empty
    }

    private function getPrivacyConfig()
    {
        return new Config();
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    protected function trackAnonymizedUserId()
    {
        $this->getPrivacyConfig()->anonymizeUserId = true;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUserId('foobar');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
    }

    protected function trackAnonymizedOrderId()
    {
        $this->getPrivacyConfig()->anonymizeOrderId = true;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.73');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/myorder');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->doTrackEcommerceOrder('myorderid', 10, 7, 2, 1, 0);
    }

    protected function trackAnonymizedReferrerExcludeAllWebsite()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.74');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrlReferrer('https://www.foo.com/bar/?baz=exclude_all');
        $t->setUrl('http://example.com/exclude_all');
        self::checkResponse($t->doTrackPageView('Exclude all referrer website'));

    }

    protected function trackAnonymizedReferrerExcludePathWebsite()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_PATH;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.75');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrlReferrer('https://www.foo.com/bar/?baz=exclude_path_website');
        $t->setUrl('http://example.com/exclude_path_website');
        self::checkResponse($t->doTrackPageView('Exclude path website'));

    }

    protected function trackAnonymizedReferrerExcludeAllSearch()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.76');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrlReferrer('http://google.com/search?q=exclude_all_search');
        $t->setUrl('http://example.com/exclude_all_search');
        self::checkResponse($t->doTrackPageView('Exclude all search'));
    }

    protected function trackAnonymizedReferrerExcludeQuerySocial()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_QUERY;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.77');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrlReferrer('https://www.facebook.com/profile?id=exclude_query_social');
        $t->setUrl('http://example.com/exclude_query_social');
        self::checkResponse($t->doTrackPageView('Exclude query social'));
    }

    protected function trackAnonymizedReferrerExcludeAllSocial()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.78');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrlReferrer('https://www.facebook.com/profile?id=exclude_query_social');
        $t->setUrl('http://example.com/exclude_query_social');
        self::checkResponse($t->doTrackPageView('Exclude query social'));
    }

    protected function trackAnonymizedReferrerExcludeAllCampaign()
    {
        $this->getPrivacyConfig()->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.78');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrlReferrer('https://www.example.com/exclude_all_campaign');
        $t->setUrl('http://example.com/exclude_query_social?mtm_kwd=campaignkeyword&mtm_campaign=campaign');
        self::checkResponse($t->doTrackPageView('Exclude query social'));
    }
}
