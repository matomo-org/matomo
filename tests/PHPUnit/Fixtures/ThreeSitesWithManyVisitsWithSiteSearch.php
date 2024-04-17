<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds three websites with different site search configurations and adds
 * several visits to each of them.
 */
class ThreeSitesWithManyVisitsWithSiteSearch extends Fixture
{
    public $idSite1 = 1;
    public $idSite2 = 2;
    public $idSite3 = 3;
    public $dateTime = '2010-01-03 11:22:33';

    public function setUp(): void
    {
        self::setUpWebsites();
        self::trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    /**
     * One site with custom search parameters,
     * One site using default search parameters,
     * One site with disabled site search
     */
    protected function setUpWebsites()
    {
        API::getInstance()->setGlobalSearchParameters($searchKeywordParameters = 'gkwd', $searchCategoryParameters = 'gcat');

        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite(Date::factory($this->dateTime)->subHour(200)->getDatetime(), 0, "Site 1 - Site search", $siteurl = false, $search = 1, $searchKwd = 'q,mykwd,p', $searchCat = 'cats');
        }

        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite(Date::factory($this->dateTime)->subHour(400)->getDatetime(), 0, "Site 2 - Site search use default", $siteurl = false, $search = 1, $searchKwd = '', $searchCat = '');
        }

        if (!self::siteCreated($idSite = 3)) {
            self::createWebsite(Date::factory($this->dateTime)->subHour(600)->getDatetime(), 0, "Site 3 - No site search", $siteurl = false, $search = 0);
        }
    }

    protected function trackVisits()
    {
        $this->recordVisitorsSite1();
        $this->recordVisitorSite2();
        $this->recordVisitorSite3();
    }

    protected function recordVisitorsSite1()
    {
        // -
        // Visitor site1
        $visitor = self::getTracker($this->idSite1, $this->dateTime, $defaultInit = true);

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?q=Search 1  ');
        self::checkResponse($visitor->doTrackPageView('Site Search results'));

        // Normal page view
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.22)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm');
        self::checkResponse($visitor->doTrackPageView('Im just a page'));

        // IS_FOLLOWING_SEARCH: Not this time
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.23)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?random=PAGEVIEW, NOT SEARCH&mykwd=&IS_FOLLOWING_SEARCH ONCE');
        self::checkResponse($visitor->doTrackPageView('This is a pageview, not a Search - IS_FOLLOWING_SEARCH ONCE'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.24)->getDatetime());
        self::checkResponse($visitor->doTrackEvent("Event CAT", "Event ACTION", "Event NAME", $count = 3.33));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.25)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?standard=query&but=also#hash&q=' . urlencode('Search 1'));
        self::checkResponse($visitor->doTrackPageView('Site Search results - URL Fragment'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.26)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm#q=Search 1&search_count=10');
        self::checkResponse($visitor->doTrackPageView('Site Search results - URL Fragment'));

        // &search_count=0 so it's a "No Result" keyword, but it will not appear in the report, because it also has other seraches with results
        // and the archiving does a MAX()
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.27)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?hello=world#q=Search 1&search_count=0');
        self::checkResponse($visitor->doTrackPageView('Site Search results - URL Fragment'));

        // Testing with non urlencoded values
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        // ALso testing that array[] notation is detected
        $visitor->setUrl('http://example.org/index.htm?random=param&mykwd[]=Search 2&test&cats= Search Category &search_count=INCORRECT!');
        self::checkResponse($visitor->doTrackPageView('Site Search results'));

        // Testing with urlencoded values
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.32)->getDatetime());
        // Also testing with random case 'myKwd'
        $visitor->setUrl('http://example.org/index.htm?random=param&myKwd=Search 1&test&cats=' . urlencode(' Search Category ') . ' &search_count=0');
        self::checkResponse($visitor->doTrackPageView('Site Search results'));

        // IS_FOLLOWING_SEARCH: Yes
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.35)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?random=PAGEVIEW, NOT SEARCH&mykwd=&IS_FOLLOWING_SEARCH ONCE');
        self::checkResponse($visitor->doTrackPageView('This is a pageview, not a Search - IS_FOLLOWING_SEARCH ONCE'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.4)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?gkwd=SHOULD be a PageView, NOT a search');
        self::checkResponse($visitor->doTrackPageView('Pageview, not search'));

        $visitor->setUrl('http://example.org/hello?THIS IS A SITE SEARCH TRACKING API, NOT PAGEVIEW!');

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(24.41)->getDatetime());
        self::checkResponse($visitor->doTrackSiteSearch("Keyword - Tracking API"));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(24.42)->getDatetime());
        self::checkResponse($visitor->doTrackSiteSearch("Keyword - Tracking API", "Category", $count = 5));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(24.425)->getDatetime());
        self::checkResponse($visitor->doTrackEvent("Event CAT", "Event ACTION", "Event NAME", $count));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(24.43)->getDatetime());
        self::checkResponse($visitor->doTrackSiteSearch("No Result Keyword!", "Bad No Result Category :(", $count = 0));

        // Keyword in iso-8859-15 charset with funny character
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(24.5)->getDatetime());
        $visitor->setPageCharset('iso-8859-15');
        $visitor->setUrl('http://example.org/index.htm?q=Final%20t%FCte%20Keyword%20Searched%20for%20now&search_count=10');
        self::checkResponse($visitor->doTrackPageView(false));

        // -
        // Visitor BIS
        $visitorB = self::getTracker($this->idSite1, $this->dateTime, $defaultInit = true);
        $visitorB->setIp('156.66.6.66');
        $visitorB->setResolution(1600, 1000);

        $visitorB->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.26)->getDatetime());
        $visitorB->setUrl('http://example.org/index.htm#q=' . urlencode('No Result Keyword!') . '&search_count=0');
        self::checkResponse($visitorB->doTrackPageView('Site Search results - URL Fragment'));

        $visitorB->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.27)->getDatetime());
        $visitorB->setUrl('http://example.org/index.htm?hello=world#q=Search 1&search_count=10');
        self::checkResponse($visitorB->doTrackPageView('Site Search results - URL Fragment'));

        $visitorB->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.3)->getDatetime());
        $visitorB->setUrl('http://example.org/index.htm?random=param&mykwd=Search 2&test&cats= Search Category &search_count=10');
        self::checkResponse($visitorB->doTrackPageView('Site Search results'));
    }

    protected function recordVisitorSite2()
    {
        $visitor = self::getTracker($this->idSite2, $this->dateTime, $defaultInit = true);
        $visitor->setResolution(801, 301);

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?q=Search 1&IsPageView=1');
        self::checkResponse($visitor->doTrackPageView('IsPageView'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.35)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?random=PAGEVIEW, NOT SEARCH&gcat=Cat not but not keyword, so this is not search');
        self::checkResponse($visitor->doTrackPageView('This is a pageview, not a Search'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.4)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?gkwd=SHOULD be a Search with no result!&search_count=0');
        self::checkResponse($visitor->doTrackPageView('This is a Search'));

        // Testing UTF8 keywords
        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.45)->getDatetime());
        $crazySearchTerm = 'You%20can%20use%20Piwik%20in%3A%20%E1%8A%A0%E1%88%9B%E1%88%AD%E1%8A%9B%2C%20%D8%A7%D9%84%D8%B9%D8%B1%D8%A8%D9%8A%D8%A9%2C%20%D0%91%D0%B5%D0%BB%D0%B0%D1%80%D1%83%D1%81%D0%BA%D0%B0%D1%8F%2C%20%D0%91%D1%8A%D0%BB%D0%B3%D0%B0%D1%80%D1%81%D0%BA%D0%B8%2C%20Catal%C3%A0%2C%20%C4%8Cesky%2C%20Dansk%2C%20Deutsch%2C%20%CE%95%CE%BB%CE%BB%CE%B7%CE%BD%CE%B9%CE%BA%CE%AC%2C%20English%2C%20Espa%C3%B1ol%2C%20Eesti%20keel%2C%20Euskara%2C%20%D9%81%D8%A7%D8%B1%D8%B3%DB%8C%2C%20Suomi%2C%20Fran%C3%A7ais%2C%20Galego%2C%20%D7%A2%D7%91%D7%A8%D7%99%D7%AA%2C%20Magyar%2C%20Bahasa%20Indonesia%2C%20%C3%8Dslenska%2C%20Italiano%2C%20%E6%97%A5%E6%9C%AC%E8%AA%9E%2C%20%E1%83%A5%E1%83%90%E1%83%A0%E1%83%97%E1%83%A3%E1%83%9A%E1%83%98%2C%20%ED%95%9C%EA%B5%AD%EC%96%B4%2C%20Lietuvi%C5%B3%2C%20Latvie%C5%A1u%2C%20Norsk%20(bokm%C3%A5l)%2C%20Nederlands%2C%20Norsk%20(nynorsk)%2C%20Polski%2C%20Portugu%C3%AAs%20brasileiro%2C%20Portugu%C3%AAs%2C%20Rom%C3%A2n%C4%83%2C%20%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9%2C%20Slovensky%2C%20Sloven%C5%A1%C4%8Dina%2C%20Shqip%2C%20Srpski%2C%20Svenska%2C%20%E0%B0%A4%E0%B1%86%E0%B0%B2%E0%B1%81%E0%B0%97%E0%B1%81%2C%20%E0%B8%A0%E0%B8%B2%E0%B8%A9%E0%B8%B2%E0%B9%84%E0%B8%97%E0%B8%A2%2C%20T%C3%BCrk%C3%A7e%2C%20%D0%A3%D0%BA%D1%80%D0%B0%D1%97%D0%BD%D1%81%D1%8C%D0%BA%D0%B0%2C%20%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87%2C%20%E7%B9%81%E9%AB%94%E4%B8%AD%E6%96%87.';
        $visitor->setUrl('http://example.org/index.htm?gkwd=' . $crazySearchTerm . '&gcat=' . $crazySearchTerm . '&search_count=1');
        self::checkResponse($visitor->doTrackPageView('Site Search with 1 result'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.5)->getDatetime());
        self::checkResponse($visitor->doTrackSiteSearch("No Result Keyword!", "Bad No Result Category bis :(", $count = 0));
        return array($defaultInit, $visitor);
    }

    protected function recordVisitorSite3()
    {
        // Third new visitor on Idsite 3
        $visitor = self::getTracker($this->idSite3, $this->dateTime, $defaultInit = true);
        $visitor->setResolution(1801, 1301);

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?q=Search 1&IsPageView=1');
        $visitor->setCustomVariable(1, 'test cvar name', 'test cvar value');
        self::checkResponse($visitor->doTrackPageView('IsPageView'));

        $visitor->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.35)->getDatetime());
        $visitor->setUrl('http://example.org/index.htm?gkwd=test not a keyword&gcat=Cat not but not keyword, so this is not search');
        self::checkResponse($visitor->doTrackPageView('This is a pageview, not a Search'));

        // Testing UTF8 Title & URL
        $crazyTitle = '%2C%20%C3%8Dslenska%2C%20Italiano%2C%20%E6%97%A5%E6%9C%AC%E8%AA%9E%2C%20%E1%83%A5%E1%83%90%E1%83%A0%E1%83%97%E1%83%A3%E1%83%9A%E1%83%98%2C%20%ED%95%9C%EA%B5%AD%EC%96%B4%2C%20Lietuvi%C5%B3%2C%20Latvie%C5%A1u%2C%20Norsk%20(bokm%C3%A5l)%2C%20Nederlands%2C%20Norsk%20(nynorsk)%2C%20Polski%2C%20Portugu%C3%AAs%20brasileiro%2C%20Portugu%C3%AAs%2C%20Rom%C3%A2n%C4%83%2C%20%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9%2C%20Slovensky%2C%20Sloven%C5%A1%C4%8Dina%2C%20Shqip%2C%20Srpski%2C%20Svenska%2C%20%E0%B0%A4%E0%B1%86%E0%B0%B2%E0%B1%81%E0%B0%97%E0%B1%81%2C%20%E0%B8%A0%E0%B8%B2%E0%B8%A9%E0%B8%B2%E0%B9%84%E0%B8%97%E0%B8%A2%2C%20T%C3%BCrk%C3%A7e%2C%20%D0%A3%D0%BA%D1%80%D0%B0%D1%97%D0%BD%D1%81%D1%8C%D0%BA%D0%B0%2C%20%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87%2C%20%E7%B9%81%E9%AB%94%E4%B8%AD%E6%96%87.';
        $visitor->setUrl('http://example.org/index.htm?' . $crazyTitle);
        self::checkResponse($visitor->doTrackPageView('Pageview: ' . $crazyTitle));
    }
}
