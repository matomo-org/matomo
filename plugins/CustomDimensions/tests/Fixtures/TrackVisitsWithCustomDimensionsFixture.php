<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Fixtures;

use Piwik\Context;
use Piwik\Date;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dimension\Extraction;
use Piwik\Plugins\Goals;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;

/**
 * Generates tracker testing data for our ApiTest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class TrackVisitsWithCustomDimensionsFixture extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $idSite2 = 2;

    public function setUp(): void
    {
        $this->setUpWebsites();
        $this->addGoals();
        $this->configureSomeDimensions();
        $this->configureScheduledReport();
        $this->trackFirstVisit();
        $this->trackSecondVisit();
        $this->trackThirdVisit();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        foreach (array($this->idSite, $this->idSite2) as $idSite) {
            if (!self::siteCreated($idSite)) {
                self::createWebsite($this->dateTime);
            }
        }
    }

    private function addGoals()
    {
        Goals\API::getInstance()->addGoal($this->idSite, 'Has sub_en', 'url', 'sub_en', 'contains');
    }

    private function configureSomeDimensions()
    {
        $configuration = new Configuration();
        $configuration->configureNewDimension($this->idSite, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, $active = true, $extractions = array(), $caseSensitive = true);

        $configuration->configureNewDimension($this->idSite, 'MyName2', CustomDimensions::SCOPE_VISIT, 2, $active = true, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($this->idSite2, 'MyName1', CustomDimensions::SCOPE_VISIT, 1, $active = true, $extractions = array(), $caseSensitive = true);

        $extraction1 = new Extraction('urlparam', 'test');
        $extraction2 = new Extraction('urlparam', 'param');
        $extraction3 = new Extraction('url', '/sub_(.{2})/page');
        $configuration->configureNewDimension($this->idSite, 'MyName3', CustomDimensions::SCOPE_ACTION, 1, $active = true, $extractions = array($extraction3->toArray()), $caseSensitive = true);
        $configuration->configureNewDimension($this->idSite, 'MyName4', CustomDimensions::SCOPE_ACTION, 2, $active = false, $extractions = array(), $caseSensitive = true);
        $configuration->configureNewDimension($this->idSite, 'MyName5', CustomDimensions::SCOPE_ACTION, 3, $active = true, $extractions = array($extraction1->toArray(), $extraction2->toArray()), $caseSensitive = true);
        $configuration->configureNewDimension($this->idSite, 'MyName6', CustomDimensions::SCOPE_VISIT, 4, $active = true, $extractions = array(), $caseSensitive = true);

        Cache::deleteCacheWebsiteAttributes(1);
        Cache::deleteCacheWebsiteAttributes(2);
        Cache::clearCacheGeneral();
    }

    protected function configureScheduledReport()
    {
        // Context change is needed, as adding the custom dimensions reports looks for the idSite in the request params
        Context::changeIdSite(1, function () {
            APIScheduledReports::getInstance()->addReport(
                $idSite = 1,
                'ScheduledReport',
                'month',
                0,
                ScheduledReports::EMAIL_TYPE,
                ReportRenderer::PDF_FORMAT,
                ['VisitsSummary_get', 'CustomDimensions_getCustomDimension_idDimension--1', 'CustomDimensions_getCustomDimension_idDimension--2'],
                [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_AND_GRAPHS]
            );
        });
    }

    protected function trackFirstVisit()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        $t->setCustomDimension('1', 'value1');
        $t->setCustomDimension('2', 'value2');
        $t->setCustomDimension('3', 'value3');
        $t->setCustomDimension('4', 'value4');
        $t->setCustomDimension('5', 'value5');
        $t->setCustomDimension('6', 'value6');

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->setCustomDimension('1', 'value5 1');
        $t->setCustomDimension('2', 'dim 2');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page?test=343&param=23');
        self::checkResponse($t->doTrackPageView('Second page view'));

        $t->setCustomDimension('2', 'en_US');
        $t->setCustomDimension('3', 'value5 3');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page?param=en_US');
        self::checkResponse($t->doTrackPageView('Third page view'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addDay(0.4)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page?param=en_US');
        self::checkResponse($t->doTrackPageView('Fourth page view'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addDay(2)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page?param=en_US');
        self::checkResponse($t->doTrackPageView('Fifth page view'));

        $t->setCustomDimension('1', 'value1');
        $t->setCustomDimension('2', 'value2');
        $t->setCustomDimension('5', 'value5 5');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addDay(3)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page?param=en_US');
        self::checkResponse($t->doTrackPageView('Sixth page view'));
    }

    protected function trackSecondVisit()
    {
        $t = self::getTracker($this->idSite2, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.73');

        $t->setCustomDimension('1', 'site2 value1');
        $t->setCustomDimension('2', 'site2 value2');
        $t->setCustomDimension('3', 'site2 value3');
        $t->setCustomDimension('4', 'site2 value4');
        $t->setCustomDimension('5', 'site2 value5');
        $t->setCustomDimension('6', 'site2 value6');

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setUrl('http://example.com/?search=this is a site search query');
        self::checkResponse($t->doTrackPageView('Site search query'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU_ID2', $name = 'A durable item', $category = 'Best seller', $price = 321);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 33 * 77));
    }

    // tracking visit with empty dimension values
    protected function trackThirdVisit()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.79');

        $t->setCustomDimension('1', '');
        $t->setCustomDimension('3', '');

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/sub_en/page');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
    }
}
