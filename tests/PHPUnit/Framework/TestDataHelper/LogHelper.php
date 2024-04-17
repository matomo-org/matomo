<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestDataHelper;

use Piwik\Common;
use Matomo\Network\IPUtils;
use Piwik\Db;

/**
 * Test helper that inserts rows into log tables. Defines defaults for all non null columns so
 * developers can specify as little as needed.
 */
class LogHelper
{
    public function insertVisit($visit = array())
    {
        $defaultProperties = array(
            'idsite' => 1,
            'idvisitor' => $this->getDummyVisitorId(),
            'visit_last_action_time' => '2012-01-01 00:00:00',
            'config_id' => $this->getDummyVisitorId(),
            'location_ip' => IPUtils::stringToBinaryIP('1.2.3.4'),
            'visitor_localtime' => '2012-01-01 00:00:00',
            'location_country' => 'xx',
            'config_os' => 'xxx',
            'visit_total_events' => 0,
            'visitor_seconds_since_last' => 0,
            'config_quicktime' => 0,
            'config_pdf' => 0,
            'config_realplayer' => 0,
            'config_silverlight' => 0,
            'config_windowsmedia' => 0,
            'config_java' => 0,
            'config_resolution' => 0,
            'config_resolution' => '',
            'config_cookie' => 0,
            'config_flash' => 0,
            'config_browser_version' => '',
            'visitor_count_visits' => 1,
            'visitor_returning' => 0,
            'visit_total_time' => 123,
            'visit_entry_idaction_name' => 0,
            'visit_entry_idaction_url' => 0,
            'visitor_seconds_since_order' => 0,
            'visitor_seconds_since_first' => 0,
            'visit_first_action_time' => '2012-01-01 00:00:00',
            'visit_goal_buyer' => 0,
            'visit_goal_converted' => 0,
            'visit_exit_idaction_name' => 0,
            'referer_url' => '',
            'location_browser_lang' => 'xx',
            'config_browser_engine' => '',
            'config_browser_name' => '',
            'referer_type' => 0,
            'referer_name' => '',
            'visit_total_actions' => 0,
            'visit_total_searches' => 0
        );

        $visit = array_merge($defaultProperties, $visit);

        $this->insertInto('log_visit', $visit);

        $idVisit = Db::fetchOne("SELECT LAST_INSERT_ID()");
        return $this->getVisit($idVisit, $allColumns = true);
    }

    private function insertInto($table, $row)
    {
        $columns = implode(', ', array_keys($row));
        $columnsPlaceholders = Common::getSqlStringFieldsArray($row);
        $values = array_values($row);

        Db::query("INSERT INTO " . Common::prefixTable($table) . " ($columns) VALUES ($columnsPlaceholders)", $values);
    }

    public function getVisit($idVisit, $allColumns = false)
    {
        $columns = $allColumns ? "*" : "location_country, location_region, location_city, location_latitude, location_longitude";
        $visit = Db::fetchRow("SELECT $columns FROM " . Common::prefixTable('log_visit') . " WHERE idvisit = ?", array($idVisit));

        return $visit;
    }

    public function insertConversion($idVisit, $properties = array())
    {
        $defaultProperties = array(
            'idvisit' => $idVisit,
            'idsite' => 1,
            'idvisitor' => $this->getDummyVisitorId(),
            'server_time' => '2012-01-01 00:00:00',
            'idgoal' => 1,
            'buster' => 1,
            'url' => '',
            'location_country' => 'xx',
            'visitor_count_visits' => 0,
            'visitor_returning' => 0,
            'visitor_seconds_since_order' => 0,
            'visitor_seconds_since_first' => 0
        );

        $properties = array_merge($defaultProperties, $properties);

        $this->insertInto('log_conversion', $properties);
    }

    private function getDummyVisitorId()
    {
        return Common::hex2bin('ea95f303f2165aa0');
    }

    public function insertVisitAction($idVisit, $properties = array())
    {
        $defaultProperties = array(
            'idsite' => 1,
            'idvisitor' => $this->getDummyVisitorId(),
            'idvisit' => $idVisit,
            'idaction_name_ref' => 1,
            'server_time' => '2012-01-01 00:00:00',
            'time_spent_ref_action' => 1
        );

        $properties = array_merge($defaultProperties, $properties);

        $this->insertInto('log_link_visit_action', $properties);
    }

    public function insertConversionItem($idVisit, $idOrder, $properties = array())
    {
        $defaultProperties = array(
            'idsite' => 1,
            'idvisitor' => $this->getDummyVisitorId(),
            'server_time' => '2012-01-01 00:00:00',
            'idvisit' => $idVisit,
            'idorder' => $idOrder,
            'idaction_sku' => 1,
            'idaction_name' => 2,
            'idaction_category' => 3,
            'idaction_category2' => 4,
            'idaction_category3' => 5,
            'idaction_category4' => 6,
            'idaction_category5' => 7,
            'price' => 40,
            'quantity' => 4,
            'deleted' => 0
        );

        $properties = array_merge($defaultProperties, $properties);

        $this->insertInto('log_conversion_item', $properties);
    }
}
