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
 * Adds one website and some visits with non unicode page titles.
 */
class SomeVisitsWithNonUnicodePageTitles extends Fixture
{
    public $idSite1 = 1;
    public $dateTime = '2010-01-03 11:22:33';

    public function setUp(): void
    {
        $this->setUpWebsites();
        $this->trackVisits();
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
    private function setUpWebsites()
    {
        API::getInstance()->setGlobalSearchParameters($searchKeywordParameters = 'gkwd', $searchCategoryParameters = 'gcat');

        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite(Date::factory($this->dateTime)->getDatetime(), 0, "Site 1 - Site search", $siteurl = false, $search = 1, $searchKwd = 'q,mykwd,p', $searchCat = 'cats');
        }
    }

    private function trackVisits()
    {
        $idSite1 = $this->idSite1;
        $dateTime = $this->dateTime;

        self::assertTrue(function_exists('mb_check_encoding'), ' check mb_check_encoding ');
        self::assertTrue(function_exists('mb_convert_encoding'), ' check mb_convert_encoding ');

        // Visitor site1
        $visitor = self::getTracker($idSite1, $dateTime, $defaultInit = true);

        // Test w/ iso-8859-15
        $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $visitor->setUrlReferrer('http://anothersite.com/whatever.html?whatever=Ato%FC');
        // Also testing that the value is encoded when passed as an array
        $visitor->setUrl('http://example.org/index.htm?random=param&mykwd[]=Search 2%FC&test&cats= Search Kategory &search_count=INCORRECT!');
        $visitor->setPageCharset('iso-8859-15');
        self::checkResponse($visitor->doTrackPageView('Site Search results'));
        $visitor->setPageCharset('');

        // Test w/ windows-1251
        $visitor = self::getTracker($idSite1, $dateTime, $defaultInit = true);
        $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $visitor->setUrlReferrer('http://anothersite.com/whatever.html?txt=%EC%E5%F8%EA%EE%E2%FB%E5');
        $visitor->setUrl('http://example.org/page/index.htm?whatever=%EC%E5%F8%EA%EE%E2%FB%E5');
        $visitor->setPageCharset('windows-1251');
        self::checkResponse($visitor->doTrackPageView('Page title is always UTF-8'));

        $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $nonUnicodeKeyword = '%EC%E5%F8%EA%EE%E2%FB%E5';
        $visitor->setUrl('http://example.org/page/index.htm?q=' . $nonUnicodeKeyword);
        $visitor->setPageCharset('windows-1251');
        self::checkResponse($visitor->doTrackPageView('Site Search'));

        // Test URL with non unicode Site Search keyword
        $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        //TESTS: on jenkins somehow the "<-was here" was cut off so removing this test case and simply append the wrong keyword
//        $visitor->setUrl('http://example.org/page/index.htm?q=non unicode keyword %EC%E5%F8%EAe%EE%E2%FBf%E5 <-was here');
        $visitor->setUrl('http://example.org/page/index.htm?q=non unicode keyword %EC%E5%F8%EA%EE%E2%FB%E5');
        $visitor->setPageCharset('utf-8');
        self::checkResponse($visitor->doTrackPageView('Site Search'));

        $visitor->setPageCharset('');
        $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        $visitor->setUrl('http://example.org/exit-page');
        self::checkResponse($visitor->doTrackPageView('Page title is always UTF-8'));

        // Test set invalid page char set
        $visitor = self::getTracker($idSite1, $dateTime, $defaultInit = true);
        $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        $visitor->setUrlReferrer('http://anothersite.com/whatever.html');
        $visitor->setUrl('http://example.org/index.htm?random=param&mykwd=a+keyword&test&cats= Search Kategory &search_count=INCORRECT!');
        $visitor->setPageCharset('GTF-42'); // galactic transformation format
        self::checkResponse($visitor->doTrackPageView('Site Search results'));
        $visitor->setPageCharset('');
    }
}
