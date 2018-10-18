<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;

/**
 * @group Core
 * @group Segment
 */
class SegmentTest extends IntegrationTestCase
{
    public $tableLogActionCacheHits = 0;

    private $exampleSegment = 'visitCount>=1';

    public function setUp()
    {
        parent::setUp();

        // setup the access layer (required in Segment contrustor testing if anonymous is allowed to use segments)
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2015-01-01 00:00:00');
    }

    static public function removeExtraWhiteSpaces($valueToFilter)
    {
        if (is_array($valueToFilter)) {
            foreach ($valueToFilter as $key => $value) {
                $valueToFilter[$key] = self::removeExtraWhiteSpaces($value);
            }
            return $valueToFilter;
        } else {
            return preg_replace('/[\s]+/', ' ', $valueToFilter);
        }
    }

    public function test_getSelectQuery_whenJoiningManyCustomTablesItShouldKeepTheOrderAsDefined()
    {
        $actionType = 3;
        $idSite = 1;
        $select = 'log_link_visit_action.custom_dimension_1,
                  log_action.name as url,
                  sum(log_link_visit_action.time_spent) as `13`,
                  sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`';
        $from  = array(
            'log_link_visit_action',
            'log_visit',
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => 'log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit',
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => 'log_link_visit_action_foo.idaction_url = log_action_foo.idaction',
            ),
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_bar',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_bar.idvisit"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_bar',
                'joinOn' => "log_link_visit_action_bar.idaction_url = log_action_bar.idaction"
            ),
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_baz',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_baz.idvisit"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_baz',
                'joinOn' => "log_link_visit_action_baz.idaction_url = log_action_baz.idaction"
            ),
            'log_action',
        );

        $where = 'log_link_visit_action.server_time >= ?
                  AND log_link_visit_action.server_time <= ?
                  AND log_link_visit_action.idsite = ?';
        $bind = array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite);

        $segment = 'actionType==' . $actionType;
        $segment = new Segment($segment, $idSites = array());

        $query = $segment->getSelectQuery($select, $from, $where, $bind);

        $logActionTable = Common::prefixTable('log_action');
        $logLinkVisitActionTable = Common::prefixTable('log_link_visit_action');
        $logVisitTable = Common::prefixTable('log_visit');

        $expected = array(
            "sql" => "
            SELECT log_link_visit_action.custom_dimension_1,
                   log_action.name as url,
                   sum(log_link_visit_action.time_spent) as `13`,
                   sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `6`
            FROM log_link_visit_action AS log_link_visit_action
            LEFT JOIN $logActionTable AS log_action ON log_link_visit_action.idaction_url = log_action.idaction
            LEFT JOIN $logVisitTable AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action_foo ON log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit
            LEFT JOIN $logActionTable AS log_action_foo ON log_link_visit_action_foo.idaction_url = log_action_foo.idaction
            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action_bar ON log_link_visit_action.idvisit = log_link_visit_action_bar.idvisit
            LEFT JOIN $logActionTable AS log_action_bar ON log_link_visit_action_bar.idaction_url = log_action_bar.idaction
            LEFT JOIN $logLinkVisitActionTable AS log_link_visit_action_baz ON log_link_visit_action.idvisit = log_link_visit_action_baz.idvisit
            LEFT JOIN $logActionTable AS log_action_baz ON log_link_visit_action_baz.idaction_url = log_action_baz.idaction
            WHERE ( log_link_visit_action.server_time >= ?
                AND log_link_visit_action.server_time <= ?
                AND log_link_visit_action.idsite = ? )
                AND ( log_action.type = ? )",
            "bind" => array('2015-11-30 11:00:00', '2015-12-01 10:59:59', $idSite, $actionType));

        $this->assertEquals($this->removeExtraWhiteSpaces($expected), $this->removeExtraWhiteSpaces($query));

        $this->assertQueryDoesNotFail($query);
    }

    private function assertWillBeArchived($segmentString)
    {
        $this->assertTrue($this->willSegmentByArchived($segmentString));
    }

    private function assertNotWillBeArchived($segmentString)
    {
        $this->assertFalse($this->willSegmentByArchived($segmentString));
    }

    private function willSegmentByArchived($segmentString)
    {
        $segment = new Segment($segmentString, $idSites = array(1));

        return $segment->willBeArchived();
    }

    private function disableBrowserArchiving()
    {
        Rules::setBrowserTriggerArchiving(false);
    }

    private function disableSegmentBrowserArchiving()
    {
        $this->disableBrowserArchiving();
        $config = Config::getInstance();
        $general = $config->General;
        $general['browser_archiving_disabled_enforce'] = '1';
        $config->General = $general;
    }

    private function assertQueryDoesNotFail($query)
    {
        Db::fetchAll($query['sql'], $query['bind']);
        $this->assertTrue(true);
    }
}
