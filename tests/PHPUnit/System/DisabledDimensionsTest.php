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
use Piwik\Plugins\CoreAdminHome\SystemSettings;
use Piwik\Plugins\Events\Columns\EventCategory;
use Piwik\Plugins\Events\Columns\EventName;
use Piwik\Plugins\UserCountry\Columns\Country;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Settings\Storage\Backend;

class DisabledDimensionsTest extends IntegrationTestCase
{
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2015-03-04 00:00:00');
    }

    public function test_disabledDimensionsSetting_preventsDimensionsFromBeingTracked()
    {
        $dimension1 = new Country();
        $dimension2 = new EventCategory();
        $dimension3 = new EventName();

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
        Fixture::checkResponse($tracker->doTrackEvent('test category', 'test action', 'test name'));

        $visitInfo = $this->getLatestVisitInfo();
        $this->assertEquals([
            'idlink_va' => '1',
            'location_country' => null,
            'category' => null,
            'action' => 'test action',
            'name' => 'test name',
        ], $visitInfo);

        self::$fixture->clearInMemoryCaches();
        Backend\Cache::clearCache();

        $settings->disabledDimensions->setValue([
            $dimension3->getId(),
        ]);
        $settings->disabledDimensions->save();

        $tracker = Fixture::getTracker(1, '2020-03-04 02:04:00');
        $tracker->setUrl('http://example.com/page');
        $tracker->setCity('Munich');
        $tracker->setCountry('DE');
        Fixture::checkResponse($tracker->doTrackEvent('test category', 'test action', 'test name'));

        $visitInfo = $this->getLatestVisitInfo();
        $this->assertEquals([
            'idlink_va' => '2',
            'location_country' => 'DE',
            'category' => 'test category',
            'action' => 'test action',
            'name' => null,
        ], $visitInfo);
    }

    private function getLatestVisitInfo()
    {
        $table = Common::prefixTable('log_link_visit_action');
        $logVisit = Common::prefixTable('log_visit');
        $logAction = Common::prefixTable('log_action');

        $sql = "SELECT idlink_va, log_visit.location_country, event_cat.name as category, event_act.name as action, event_name.name as name
                  FROM $table log_link_visit_action
             LEFT JOIN $logVisit log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
             LEFT JOIN $logAction event_cat ON event_cat.idaction = log_link_visit_action.idaction_event_category
             LEFT JOIN $logAction event_act ON event_act.idaction = log_link_visit_action.idaction_event_action
             LEFT JOIN $logAction event_name ON event_name.idaction = log_link_visit_action.idaction_name
              ORDER BY log_link_visit_action.idlink_va DESC
                 LIMIT 1";
        return Db::fetchRow($sql);
    }
}