<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Common;
use Piwik\DataAccess\ArchiveInvalidator;
use Piwik\Db;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CoreAdminHome\Utility\DuplicateActionRemover;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DuplicateActionRemoverTest extends IntegrationTestCase
{
    const DUMMY_IDVISITOR = '008c5926ca861023c1d2a36653fd88e2';

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
                'visitor_days_since_order' => 1,
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
                'visitor_days_since_order' => 1,
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

    /**
     * @var DuplicateActionRemover
     */
    private $remover;

    public function setUp()
    {
        parent::setUp();

        foreach (self::$dataToInsert as $table => $rows) {
            self::insertRowData($table, $rows);
        }

        $archiveInvalidator = new ArchiveInvalidator();
        $this->remover = new DuplicateActionRemover($archiveInvalidator);
    }

    public function test_DuplicateActionRemover_CorrectlyRemovesDuplicates_AndFixesReferencesInOtherTables()
    {
        list($duplicatesCount, $affectedArchives) = $this->remover->removeDuplicateActionsFromDb();

        $this->assertDuplicateActionsRemovedFromLogActionTable();
        $this->assertDuplicatesFixedInLogLinkVisitActionTable();
        $this->assertDuplicatesFixedInLogConversionTable();
        $this->assertDuplicatesFixedInLogConversionItemTable();

        $this->assertEquals(7, $duplicatesCount);

        $expectedAffectedArchives = array(
            array('idsite' => '1', 'server_time' => '2012-01-01'),
            array('idsite' => '2', 'server_time' => '2013-01-01'),
            array('idsite' => '1', 'server_time' => '2012-02-01'),
            array('idsite' => '2', 'server_time' => '2012-01-09'),
            array('idsite' => '2', 'server_time' => '2012-03-01'),
        );
        $this->assertEquals($expectedAffectedArchives, $affectedArchives);
    }

    private static function insertRowData($unprefixedTable, $rows)
    {
        $table = Common::prefixTable($unprefixedTable);
        foreach ($rows as $row) {
            if ($unprefixedTable == 'log_action') {
                $row['hash'] = crc32($row['name']);
            }

            $placeholders = array_map(function () { return "?"; }, $row);
            $sql = "INSERT INTO $table (" . implode(',', array_keys($row)) . ") VALUES (" . implode(',', $placeholders) . ")";
            Db::query($sql, array_values($row));
        }
    }

    private function assertDuplicateActionsRemovedFromLogActionTable()
    {
        $actions = Db::fetchAll("SELECT idaction, name FROM " . Common::prefixTable('log_action'));
        $expectedActions = array(
            array('idaction' => 1, 'name' => 'action1'),
            array('idaction' => 4, 'name' => 'action2'),
            array('idaction' => 5, 'name' => 'ACTION2'),
            array('idaction' => 6, 'name' => 'action4'),
            array('idaction' => 8, 'name' => 'action5'),
        );
        $this->assertEquals($expectedActions, $actions);
    }

    private function assertDuplicatesFixedInLogLinkVisitActionTable()
    {
        $columns = array(
            'idaction_url_ref',
            'idaction_name_ref',
            'idaction_name',
            'idaction_url',
            'idaction_event_action',
            'idaction_event_category',
            'idaction_content_interaction',
            'idaction_content_name',
            'idaction_content_piece',
            'idaction_content_target'
        );
        $rows = Db::fetchAll("SELECT " . implode(',', $columns) . " FROM " . Common::prefixTable('log_link_visit_action'));
        $expectedRows = array(
            array(
                'idaction_url_ref' => '1',
                'idaction_name_ref' => '1',
                'idaction_name' => '1',
                'idaction_url' => '4',
                'idaction_event_action' => '5',
                'idaction_event_category' => '6',
                'idaction_content_interaction' => '5',
                'idaction_content_name' => '8',
                'idaction_content_piece' => '4',
                'idaction_content_target' => '6'
            ),
            array(
                'idaction_url_ref' => '1',
                'idaction_name_ref' => '1',
                'idaction_name' => '5',
                'idaction_url' => '5',
                'idaction_event_action' => '4',
                'idaction_event_category' => '6',
                'idaction_content_interaction' => '5',
                'idaction_content_name' => '5',
                'idaction_content_piece' => '6',
                'idaction_content_target' => '6'
            )
        );
        $this->assertEquals($expectedRows, $rows);
    }

    private function assertDuplicatesFixedInLogConversionTable()
    {
        $rows = Db::fetchAll("SELECT idaction_url FROM " . Common::prefixTable('log_conversion'));
        $expectedRows = array(
            array('idaction_url' => 4),
            array('idaction_url' => 5)
        );
        $this->assertEquals($expectedRows, $rows);
    }

    private function assertDuplicatesFixedInLogConversionItemTable()
    {
        $columns = array(
            'idaction_sku',
            'idaction_name',
            'idaction_category',
            'idaction_category2',
            'idaction_category3',
            'idaction_category4',
            'idaction_category5'
        );
        $rows = Db::fetchAll("SELECT " . implode(',', $columns) . " FROM " . Common::prefixTable('log_conversion_item'));
        $expectedRows = array(
            array(
                'idaction_sku' => '1',
                'idaction_name' => '1',
                'idaction_category' => '1',
                'idaction_category2' => '4',
                'idaction_category3' => '5',
                'idaction_category4' => '6',
                'idaction_category5' => '5'
            ),
            array(
                'idaction_sku' => '1',
                'idaction_name' => '1',
                'idaction_category' => '5',
                'idaction_category2' => '5',
                'idaction_category3' => '8',
                'idaction_category4' => '4',
                'idaction_category5' => '6'
            )
        );
        $this->assertEquals($expectedRows, $rows);
    }
}