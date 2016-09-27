<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tests\Framework\Fixture;

/**
 * This fixture adds one website and tracks two visits by one visitor.
 */
class OneVisitorTwoVisits extends Fixture
{
    public $idSite = 1;
    public $idSiteEmptyBis;
    public $idSiteEmptyTer;
    public $dateTime = '2010-03-06 11:22:33';

    public $useThirdPartyCookies = false;
    public $useSiteSearch = false;
    public $excludeMozilla = false;
    public $simulateIntegerOverflow = false;
    public $maxUnsignedIntegerValue = '4294967295';

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->simulateIntegerOverflow();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function simulateIntegerOverflow()
    {
        if(!$this->simulateIntegerOverflow) {
            return;
        }

        $overflow = $this->maxUnsignedIntegerValue;

        // overflow in log_visit
        $table = Common::prefixTable('log_visit');
        Db::query("INSERT INTO $table (idvisit) VALUES ($overflow)");

        // overflow in log_link_visit_action
        $table = Common::prefixTable('log_link_visit_action');
        Db::query("INSERT INTO $table (idlink_va) VALUES ($overflow)");

    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::siteCreated($idSite = 2)) {
            $this->idSiteEmptyBis = $this->createWebsite($this->dateTime);
        }

        if (!self::siteCreated($idSite = 3)) {
            $this->idSiteEmptyTer = $this->createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        if ($this->excludeMozilla) {
            APISitesManager::getInstance()->setSiteSpecificUserAgentExcludeEnabled(false);
        }

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        if ($this->useThirdPartyCookies) {
            $t->DEBUG_APPEND_URL = '&forceUseThirdPartyCookie=1';
        }

        $t->disableCookieSupport();

        $t->setUrlReferrer('http://referrer.com/page.htm?param=valuewith some spaces');

        // testing URL excluded parameters
        $parameterToExclude = 'excluded_parameter';
        APISitesManager::getInstance()->updateSite(
            $idSite,
            'new name',
            $url = array('http://site.com'),
            $ecommerce = 0,
            $siteSearch = $this->useSiteSearch ? 1 : 0,
            $searchKeywordParameters = $this->useSiteSearch ? '' : null,
            $searchCategoryParameters = $this->useSiteSearch ? 'notparam' : null,
            $excludedIps = null,
            $parameterToExclude . ',anotherParameter',
            $timezone = null,
            $currency = null,
            $group = null,
            $startDate = null,
            // test that visit won't be excluded since site-specific exclude is not enabled
            $excludedUserAgents = $this->excludeMozilla ? 'mozilla' : null
        );

        // Record 1st page view
        $urlPage1 = 'http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY&parameter=Should display';
        $t->setUrl($urlPage1);
		$t->setGenerationTime(234);
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // testing that / and index.htm above record with different URLs
        // Recording the 2nd page after 3 minutes
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $t->setUrl('http://example.org/');
		$t->setGenerationTime(224);
        self::checkResponse($t->doTrackPageView('Second page view - should be registered as URL /'));

        // Click on external link after 6 minutes (3rd action)
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.1)->getDatetime());

        // Testing Outlink that contains a URL Fragment
        self::checkResponse($t->doTrackAction('https://outlinks.org/#!outlink-with-fragment-<script>', 'link'));

        // Click on file download after 12 minutes (4th action)
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.2)->getDatetime());
        self::checkResponse($t->doTrackAction('http://piwik.org/path/again/latest.zip', 'download'));

        // Click on two more external links, one the same as before (5th & 6th actions)
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.22)->getDateTime());
        self::checkResponse($t->doTrackAction('http://outlinks.org/other_outlink#fragment&pk_campaign=Open%20partnership', 'link'));
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.25)->getDateTime());
        self::checkResponse($t->doTrackAction('http://dev.piwik.org/svn', 'link'));

        // Create Goal 1: Triggered by JS, after 18 minutes
        $idGoal = 1;
        if (!self::goalExists($idSite, $idGoal)) {
            $idGoal = APIGoals::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        }

        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());

        // Change to Thai  browser to ensure the conversion is credited to FR instead (the visitor initial country)
        $t->setBrowserLanguage('th');
        self::checkResponse($t->doTrackGoal($idGoal, $revenue = 42));

        // Track same Goal twice (after 24 minutes), should only be tracked once
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal, $revenue = 42));

        $t->setBrowserLanguage('fr');

        if ($this->useSiteSearch) {
            // Site Search request
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.42)->getDatetime());
            $t->setUrl('http://example.org/index.htm?q=Banks Own The World');
			$t->setGenerationTime(812);
            self::checkResponse($t->doTrackPageView('Site Search request'));

            // Final page view (after 27 min)
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.45)->getDatetime());
            $t->setUrl('http://example.org/index.htm');
			$t->setGenerationTime(24);
            self::checkResponse($t->doTrackPageView('Looking at homepage after site search...'));
        } else {
            // Final page view (after 27 min)
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.45)->getDatetime());
            $t->setUrl('http://example.org/index.htm#ignoredFragment#');
			$t->setGenerationTime(23);
            self::checkResponse($t->doTrackPageView('Looking at homepage (again)...'));
        }

        // -
        // End of first visit: 24min

        // Create Goal 2: Matching on URL
        if (!self::goalExists($idSite, $idGoal = 2)) {
            APIGoals::getInstance()->addGoal($idSite, 'matching purchase.htm', 'url', '(.*)store\/purchase\.(.*)', 'regex', false, $revenue = 1);
        }

        // -
        // Start of returning visit, 1 hour after first page view
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrl('http://example.org/store/purchase.htm');
        $t->setUrlReferrer('http://search.yahoo.com/search?p=purchase');
        // Temporary, until we implement 1st party cookies in PiwikTracker
        $t->DEBUG_APPEND_URL = '&_idvc=2';

        // Goal Tracking URL matching, testing custom referrer including keyword
		$t->setGenerationTime(134);
        self::checkResponse($t->doTrackPageView('Checkout/Purchasing...'));
        // -
        // End of second visit
    }
}