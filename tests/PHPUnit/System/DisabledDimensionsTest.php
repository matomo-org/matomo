<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\CustomDimensions\API;
use Piwik\Plugins\DevicesDetection\Columns\BrowserName;
use Piwik\Plugins\DevicesDetection\Columns\Os;
use Piwik\Plugins\PrivacyManager\SystemSettings;
use Piwik\Plugins\Events\Columns\EventCategory;
use Piwik\Plugins\Events\Columns\EventName;
use Piwik\Plugins\UserCountry\Columns\Country;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Settings\Storage\Backend;
use Piwik\Tracker\Cache;

class DisabledDimensionsTest extends IntegrationTestCase
{
    private static $idSite;
    private static $idDimension1;
    private static $idDimension2;

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        self::$idSite = Fixture::createWebsite('2015-03-04 00:00:00');

        self::$idDimension1 = API::getInstance()->configureNewCustomDimension(self::$idSite, 'testdim', 'visit', 1);
        self::$idDimension2 = API::getInstance()->configureNewCustomDimension(self::$idSite, 'testdim2', 'action', 1);

    }

    public function test_disabledDimensionsSetting_preventsDimensionsFromBeingTracked()
    {
        $dimension1 = new Country();
        $dimension2 = new BrowserName();
        $dimension3 = new Os();

        $settings = new SystemSettings();

        $settings->disabledDimensions->setValue([
            $dimension1->getId(),
            $dimension2->getId(),
        ]);
        $settings->save();

        $tracker = Fixture::getTracker(1, '2020-03-04 02:00:00');
        $tracker->setUrl('http://example.com/page');
        $tracker->setCity('Munich');
        $tracker->setCountry('DE');
        $tracker->setUserAgent('Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+');
        Fixture::checkResponse($tracker->doTrackPageView('page view 1'));

        $visitInfo = $this->getLatestVisitInfo();
        $this->assertEquals([
            'idlink_va' => '1',
            'location_country' => null,
            'name' => 'page view 1',
            'config_browser_name' => null,
            'config_os' => 'IOS',
        ], $visitInfo);

        self::$fixture->clearInMemoryCaches();
        Backend\Cache::clearCache();

        $settings->disabledDimensions->setValue([
            $dimension3->getId(),
        ]);
        $settings->disabledDimensions->save();

        self::$fixture->clearInMemoryCaches();
        Backend\Cache::clearCache();

        $tracker = Fixture::getTracker(1, '2020-03-04 02:04:00');
        $tracker->setForceNewVisit();
        $tracker->setUrl('http://example.com/page');
        $tracker->setCity('Munich');
        $tracker->setCountry('DE');
        $tracker->setUserAgent('Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+');
        Fixture::checkResponse($tracker->doTrackPageView('page view 2'));

        $visitInfo = $this->getLatestVisitInfo();
        $this->assertEquals([
            'idlink_va' => '2',
            'location_country' => 'DE',
            'name' => 'page view 2',
            'config_browser_name' => 'MF',
            'config_os' => null,
        ], $visitInfo);
    }

    /**
     * @depends test_disabledDimensionsSetting_preventsDimensionsFromBeingTracked
     */
    public function test_disabledDimensionsSetting_preventsDimensionsFromBeingTracked_WhenAllDimensionsSelected()
    {
        // disable all dimensions
        $settings = new SystemSettings();
        $settings->disabledDimensions->setValue(array_keys($settings->getAvailableDimensionsToDisable()));
        $settings->save();

        $dateTime = '2020-03-04 02:00:00';

        $tracker = Fixture::getTracker(1, $dateTime);
        $tracker->setUrl('http://example.com/page');
        $tracker->setCity('Munich');
        $tracker->setRegion('DE-BY');
        $tracker->setCountry('DE');
        $tracker->setLatitude(34);
        $tracker->setLongitude(35);
        $tracker->setBrowserHasCookies(true);
        $tracker->setBrowserLanguage('fr');
        $tracker->setUrlReferrer('http://myreferrer.com');
        $tracker->setPerformanceTimings(100, 200, 300, 400, 500, 600);
        $tracker->setCustomVariable(1, 'cvarname', 'cvarvalue', 'visit');
        $tracker->setCustomVariable(2, 'cvarname2', 'cvarvalue2', 'action');
        $tracker->setCustomDimension(self::$idDimension1, 'somevalue');
        $tracker->setCustomDimension(self::$idDimension2, 'someotherval');
        $tracker->setUserAgent('Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+');
        Fixture::checkResponse($tracker->doTrackPageView('page title'));

        $tracker->setNewVisitorId();
        Fixture::checkResponse($tracker->doTrackEvent('test category', 'test action', 'test name'));

        $tracker->setNewVisitorId();
        Fixture::checkResponse($tracker->doTrackAction('http://whatever.com/mydownload/thing.pdf', 'download'));

        $tracker->setNewVisitorId();
        Fixture::checkResponse($tracker->doTrackAction('http://tosomewhereelse.com/you/are/here', 'link'));

        $apiTestsToRun = [
            [
                [
                    'Actions.getPageUrls',
                    'Actions.getPageTitles',
                    'UserCountry.getCountry',
                    'UserCountry.getRegion',
                    'UserCountry.getCity',
                    'UserLanguage.getLanguage',
                    'Referrers.getAll',
                    'PagePerformance.get',
                    'CustomVariables.getCustomVariables',
                    'DevicesDetection.getType',
                    'DevicesDetection.getBrand',
                    'DevicesDetection.getModel',
                    'DevicesDetection.getOsVersions',
                    'DevicesDetection.getBrowserVersions',
                ],
                [
                    'idSite' => self::$idSite,
                    'date' => $dateTime,
                ],
            ],
            [
                'CustomDimensions.getCustomDimension',
                [
                    'idSite' => self::$idSite,
                    'date' => $dateTime,
                    'otherRequestParameters' => [
                        'iddimension' => self::$idDimension1,
                    ],
                ],
            ],
            [
                'CustomDimensions.getCustomDimension',
                [
                    'idSite' => self::$idSite,
                    'date' => $dateTime,
                    'otherRequestParameters' => [
                        'iddimension' => self::$idDimension2,
                    ],
                ],
            ],
        ];

        foreach ($apiTestsToRun as $api => $options) {
            $this->runApiTests($api, $options);
        }
    }

    private function getLatestVisitInfo()
    {
        $table = Common::prefixTable('log_link_visit_action');
        $logVisit = Common::prefixTable('log_visit');
        $logAction = Common::prefixTable('log_action');

        $sql = "SELECT idlink_va, log_visit.location_country, log_visit.config_browser_name, log_visit.config_os, event_name.name as name
                  FROM $table log_link_visit_action
             LEFT JOIN $logVisit log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
             LEFT JOIN $logAction event_name ON event_name.idaction = log_link_visit_action.idaction_name
              ORDER BY log_link_visit_action.idlink_va DESC
                 LIMIT 1";
        return Db::fetchRow($sql);
    }
}