<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Annotations\API as APIAnnotations;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Tests\Framework\XssTesting;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsAPI;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixtures/ManySitesImportedLogs.php';

/**
 * Imports visits from several log files using the python log importer &
 * adds goals/sites/etc. attempting to create XSS.
 */
class ManySitesImportedLogsWithXssAttempts extends ManySitesImportedLogs
{
    public $now = null;

    public function __construct()
    {
        $this->now = Date::factory('now');
    }

    public function setUp(): void
    {
        $this->addCustomDimensions();

        parent::setUp();

        $this->trackVisitWithActionsXss();

        $this->trackVisitsForRealtimeMap(Date::factory('2012-08-11 11:22:33'), $createSeperateVisitors = false);

        $this->addAnnotations();
        $this->trackVisitsForRealtimeMap($this->now);
    }

    public function addCustomDimensions()
    {
        if (!self::customDimensionExists($idSite = 1, $idCustomDimension = 1)) {
            CustomDimensionsAPI::getInstance()->configureNewCustomDimension($idSite = 1, 'test dim', 'visit', true);
        }
    }

    private function customDimensionExists($idSite, $idCustomDimension)
    {
        $configured = CustomDimensionsAPI::getInstance()->getConfiguredCustomDimensions($idSite);
        foreach ($configured as $dimension) {
            if ($idCustomDimension == $dimension['idcustomdimension']) {
                return true;
            }
        }
        return false;
    }

    public function setUpWebsitesAndGoals()
    {
        $xssTesting = new XssTesting();

        // for conversion testing
        if (!self::siteCreated($idSite = 1)) {
            $siteName = $xssTesting->forTwig("site name", $sanitize = true);
            self::createWebsite($this->dateTime, $ecommerce = 1, $siteName);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            APIGoals::getInstance()->addGoal(
                $this->idSite, $xssTesting->forTwig("goal name"), 'url', 'http', 'contains', false, 5, false, $xssTesting->forTwig("goal description"));
        }

        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = $xssTesting->forAngular('Piwik test two'),
                $siteUrl = 'http://example-site-two.com');
        }

        if (!self::goalExists($idSite = 2, $idGoal = 2)) {
            APIGoals::getInstance()->addGoal(
                $this->idSite, $xssTesting->forAngular("second goal"), 'url', 'http', 'contains', false, 5, false, $xssTesting->forAngular("goal description"));
        }

        if (!self::siteCreated($idSite = 3)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = 'Piwik test three',
                $siteUrl = 'http://example-site-three.com');
        }
    }

    public function addAnnotations()
    {
        $xssTesting = new XssTesting();
        APIAnnotations::getInstance()->add($this->idSite, '2012-08-09', "Note 1", $starred = 1);
        APIAnnotations::getInstance()->add(
            $this->idSite, '2012-08-08', $xssTesting->forTwig("annotation"), $starred = 0);
        APIAnnotations::getInstance()->add($this->idSite, '2012-08-10', $xssTesting->forAngular("Annotation note 3"), $starred = 1);
    }

    public function trackVisitsForRealtimeMap($date, $createSeperateVisitors = true)
    {
        $dateTime = $date->addHour(-1.25)->getDatetime();
        $idSite = 2;

        $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(-3)->getDatetime(), $defaultInit = true, $useLocal = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index1.htm');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        if ($createSeperateVisitors) {
            $t = self::getTracker($idSite, $dateTime, $defaultInit = true, $useLocal = true);
        } else {
            $t->setForceVisitDateTime($dateTime);
        }

        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index1.htm');
        $t->setCountry('jp');
        $t->setRegion("40");
        $t->setCity('Tokyo');
        $t->setLatitude(35.70);
        $t->setLongitude(139.71);
        self::checkResponse($t->doTrackPageView('incredible title!'));

        if ($createSeperateVisitors) {
            $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(0.5)->getDatetime(), $defaultInit = true, $useLocal = true);
        } else {
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        }

        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index2.htm');
        $t->setCountry('ca');
        $t->setRegion("QC");
        $t->setCity('Montreal');
        $t->setLatitude(45.52);
        $t->setLongitude(-73.58);
        self::checkResponse($t->doTrackPageView('incredible title!'));

        if ($createSeperateVisitors) {
            $t = self::getTracker($idSite, Date::factory($dateTime)->addHour(1)->getDatetime(), $defaultInit = true, $useLocal = true);
        } else {
            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        }

        $t->setTokenAuth(self::getTokenAuth());
        $t->setUrl('http://example.org/index3.htm');
        $t->setCountry('br');
        $t->setRegion("27");
        $t->setCity('Sao Paolo');
        $t->setLatitude(-23.55);
        $t->setLongitude(-46.64);
        self::checkResponse($t->doTrackPageView('incredible title!'));
    }

    private function trackVisitWithActionsXss()
    {
        $xssTesting = new XssTesting();

        $baseDay = Date::factory($this->dateTime)->addDay(10);

        $xssTypes = ['forTwig', 'forAngular'];
        foreach ($xssTypes as $index => $type) {
            $urlXss = $xssTesting->$type('pageurl');
            $titleXss = $xssTesting->$type('page title');
            $referrerUrlXss = 'http://referrer.com/' . $xssTesting->$type('referrerUrl');
            $referrerUrlSearchEngineXss = 'http://google.com/search?q=' . urlencode($xssTesting->$type('keyword'));

            $dateTime = $baseDay->addDay($index);

            $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);
            $t->setUrl('http://example.org/' . urlencode($urlXss));
            $t->setUrlReferrer($referrerUrlXss);
            $t->setCustomDimension('1', $xssTesting->$type('customdimension'));
            $t->setCustomVariable(1, $xssTesting->$type('customvarname'), $xssTesting->$type('customvarval'));
            $t->setUserId($xssTesting->$type('userid'));
            $t->setBrowserLanguage($xssTesting->$type('lang'));
            $t->setCity($xssTesting->$type('city'));
            $t->setRegion($xssTesting->$type('region'));
            $t->setCountry($xssTesting->$type('country'));
            $t->setUserAgent($xssTesting->$type('useragent'));
            self::checkResponse($t->doTrackPageView(urlencode($titleXss)));

            $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDateTime());
            $t->setUrlReferrer($referrerUrlSearchEngineXss);
            $t->setUrl('http://example.org/' . $urlXss);
            self::checkResponse($t->doTrackPageView($titleXss));
        }
    }
}
