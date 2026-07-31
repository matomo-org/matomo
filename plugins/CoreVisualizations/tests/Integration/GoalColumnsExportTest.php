<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\API\DataTablePostProcessor;
use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Covers the goal-specific column handling used by the goals table and graph visualizations:
 * that requesting a dimension report for a specific goal actually produces the
 * goal_<idGoal>_nb_conversions column through the archiving/DataTablePostProcessor pipeline, that
 * the export column restriction keeps only the goal's columns, and that restricting columns via
 * showColumns does not drop the columns a flattened report adds for its dimensions.
 *
 * @group CoreVisualizations
 * @group Goals
 */
class GoalColumnsExportTest extends IntegrationTestCase
{
    private $idSite = 1;
    private $dateTime = '2014-01-05 00:00:00';
    private $idGoal;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite($this->dateTime);
        }

        $this->idGoal = GoalsApi::getInstance()->addGoal(
            $this->idSite,
            'Reached example page',
            'url',
            'example.org',
            'contains',
            false,
            false
        );
    }

    private function trackGoalConversion(): void
    {
        // a direct visit that converts the goal (attributed to the "Direct Entry" referrer type)
        $tracker = Fixture::getTracker($this->idSite, $this->dateTime, true);
        $tracker->setUrl('http://example.org/thank-you');
        Fixture::checkResponse($tracker->doTrackPageView('Thank you'));
    }

    public function testDimensionReportForSpecificGoalProducesGoalConversionColumn()
    {
        $this->trackGoalConversion();

        $table = $this->requestReferrerTypes([
            'idGoal'                                    => $this->idGoal,
            'filter_update_columns_when_show_all_goals' => 1,
            'filter_show_goal_columns_process_goals'    => $this->idGoal,
        ]);

        $row = $table->getFirstRow();
        $column = 'goal_' . $this->idGoal . '_nb_conversions';

        // the per-goal column is materialised through DataTablePostProcessor, not just requested
        $this->assertNotFalse($row->getColumn($column), $column . ' should be produced');
        $this->assertEquals(1, $row->getColumn($column));
    }

    public function testExportShowColumnsKeepsGoalColumnsAndDropsAggregate()
    {
        $this->trackGoalConversion();

        $column = 'goal_' . $this->idGoal . '_nb_conversions';

        $table = $this->requestReferrerTypes([
            'idGoal'                                    => $this->idGoal,
            'filter_update_columns_when_show_all_goals' => 1,
            'filter_show_goal_columns_process_goals'    => $this->idGoal,
            // mirrors what the goals table / graph set via export_parameters_to_modify
            'showColumns'                               => 'label,nb_visits,' . $column,
        ]);

        $row = $table->getFirstRow();
        $this->assertNotFalse($row->getColumn($column));
        $this->assertFalse($row->getColumn('nb_conversions'), 'aggregate nb_conversions must be excluded from the export');
    }

    public function testShowColumnsDoesNotDropFlattenedDimensionColumns()
    {
        // A flattened report adds a column per dimension (names only exist after flattening), which
        // an allowlist cannot know up front; DataTablePostProcessor must keep them.
        $table = new DataTable();
        $table->addRowFromSimpleArray([
            'label'      => 'foo',
            'nb_visits'  => 5,
            'mydimension' => 'bar',
            'nb_actions' => 9,
        ]);
        $table->setMetadata('dimensions', ['mydimension']);

        $processor = new DataTablePostProcessor('Referrers', 'getReferrerType', ['showColumns' => 'label,nb_visits']);
        $processor->applyRequestedColumnDeletion($table);

        $row = $table->getFirstRow();
        $this->assertSame('bar', $row->getColumn('mydimension'), 'flattened dimension column should be kept');
        $this->assertEquals(5, $row->getColumn('nb_visits'));
        $this->assertFalse($row->getColumn('nb_actions'), 'a non-allowlisted metric should still be removed');
    }

    private function requestReferrerTypes(array $extraParams): DataTable
    {
        return Request::processRequest('Referrers.getReferrerType', array_merge([
            'idSite'       => $this->idSite,
            'period'       => 'day',
            'date'         => $this->dateTime,
            'format'       => 'original',
            'filter_limit' => -1,
        ], $extraParams));
    }
}
