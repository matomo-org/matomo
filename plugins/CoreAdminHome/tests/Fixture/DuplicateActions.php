<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\tests\Fixture;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds log table rows that use duplicate actions.
 */
class DuplicateActions extends Fixture
{
    const DUMMY_IDVISITOR = 'c1d2a36653fd88e2';

    private static $dataToInsert = array(
        'log_action' => array(
            array('name' => 'action1', 'type' => 1),
            array('name' => 'action1', 'type' => 1),
            array('name' => 'action1', 'type' => 1),

            array('name' => 'action2', 'type' => 2),
            array('name' => 'ACTION2', 'type' => 1),
            array('name' => 'action4', 'type' => 3),
            array('name' => 'ACTION2', 'type' => 1),
            array('name' => 'action5', 'type' => 2),

            array('name' => 'action2', 'type' => 2),
            array('name' => 'action4', 'type' => 3),
            array('name' => 'ACTION2', 'type' => 1),
            array('name' => 'action4', 'type' => 3),
        ),
        'log_link_visit_action' => array(
            array(
                'idsite' => 1,
                'idvisitor' => self::DUMMY_IDVISITOR,
                'idvisit' => 1,
                'server_time' => '2012-01-01 00:00:00',
                'time_spent_ref_action' => 100,
                'idaction_url_ref' => 1,
                'idaction_name_ref' => 2,
                'idaction_name' => 3,
                'idaction_url' => 4,
                'idaction_event_action' => 5,
                'idaction_event_category' => 6,
                'idaction_content_interaction' => 7,
                'idaction_content_name' => 8,
                'idaction_content_piece' => 9,
                'idaction_content_target' => 10,
            ),
            array(
                'idsite' => 2,
                'idvisitor' => self::DUMMY_IDVISITOR,
                'idvisit' => 2,
                'server_time' => '2013-01-01 00:00:00',
                'time_spent_ref_action' => 120,
                'idaction_url_ref' => 2,
                'idaction_name_ref' => 3,
                'idaction_name' => 5,
                'idaction_url' => 7,
                'idaction_event_action' => 9,
                'idaction_event_category' => 10,
                'idaction_content_interaction' => 11,
                'idaction_content_name' => 11,
                'idaction_content_piece' => 12,
                'idaction_content_target' => 12,
            ),
        ),
        'log_conversion' => array(
            array(
                'idvisit' => 1,
                'idsite' => 1,
                'idvisitor' => self::DUMMY_IDVISITOR,
                'server_time' => '2012-02-01 00:00:00',
                'idgoal' => 1,
                'buster' => 1,
                'url' => 'http://example.com/',
                'location_country' => 'nz',
                'visitor_count_visits' => 1,
                'visitor_returning' => 1,
                'visitor_seconds_since_order' => 1,
                'visitor_seconds_since_first' => 1,
                'idaction_url' => 4,
            ),

            array(
                'idvisit' => 2,
                'idsite' => 2,
                'idvisitor' => self::DUMMY_IDVISITOR,
                'server_time' => '2012-03-01 00:00:00',
                'idgoal' => 2,
                'buster' => 2,
                'url' => 'http://example.com/',
                'location_country' => 'nz',
                'visitor_count_visits' => 1,
                'visitor_returning' => 1,
                'visitor_seconds_since_order' => 1,
                'visitor_seconds_since_first' => 1,
                'idaction_url' => 7,
            )
        ),
        'log_conversion_item' => array(
            array(
                'idsite' => 1,
                'idvisitor' => self::DUMMY_IDVISITOR,
                'server_time' => '2012-02-01 00:00:00',
                'idvisit' => 1,
                'idorder' => 1,
                'price' => 10,
                'quantity' => 2,
                'deleted' => 0,
                'idaction_sku' => 1,
                'idaction_name' => 2,
                'idaction_category' => 3,
                'idaction_category2' => 4,
                'idaction_category3' => 5,
                'idaction_category4' => 6,
                'idaction_category5' => 7,
            ),
            array(
                'idsite' => 2,
                'idvisitor' => self::DUMMY_IDVISITOR,
                'server_time' => '2012-01-09 00:00:00',
                'idvisit' => 2,
                'idorder' => 2,
                'price' => 10,
                'quantity' => 1,
                'deleted' => 1,
                'idaction_sku' => 2,
                'idaction_name' => 3,
                'idaction_category' => 5,
                'idaction_category2' => 7,
                'idaction_category3' => 8,
                'idaction_category4' => 9,
                'idaction_category5' => 10,
            )
        )
    );

    public function setUp(): void
    {
        parent::setUp();

        foreach (self::$dataToInsert as $table => $rows) {
            self::insertRowData($table, $rows);
        }
    }

    private static function insertRowData($unprefixedTable, $rows)
    {
        $table = Common::prefixTable($unprefixedTable);
        foreach ($rows as $row) {
            if ($unprefixedTable == 'log_action') {
                $row['hash'] = crc32($row['name']);
            }

            if (isset($row['idvisitor'])) {
                $row['idvisitor'] = pack("H*", $row['idvisitor']);
            }

            $placeholders = array_map(function () {
                return "?";
            }, $row);
            $sql = "INSERT INTO $table (" . implode(',', array_keys($row)) . ") VALUES (" . implode(',', $placeholders) . ")";
            Db::query($sql, array_values($row));
        }
    }
}
