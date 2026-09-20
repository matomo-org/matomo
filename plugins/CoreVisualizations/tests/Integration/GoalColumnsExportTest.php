<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

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
 * showColumns drops the columns a flattened report adds for its dimensions unless the request opts
 * in with keep_flattened_dimension_columns.
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

        // tracking a visit in the past requires an authenticated request, and IntegrationTestCase
        // does not create the super user its token belongs to
        Fixture::createSuperUser();

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
        // a visit from a search engine (so the referrer type report has a subtable, ie, more than
        // one dimension when flattened) that converts the goal
        $tracker = Fixture::getTracker($this->idSite, $this->dateTime, true);
        $tracker->setUrlReferrer('https://www.google.com/search?q=matomo');
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

    public function testShowColumnsKeepsFlattenedDimensionColumnsWhenRequested()
    {
        // A flattened report adds a column per dimension, whose names only exist after flattening
        // and so cannot be part of a showColumns allowlist. keep_flattened_dimension_columns lets a
        // caller (ie, the goals table and graph exports) keep them anyway.
        $this->trackGoalConversion();

        $dimensions = $this->getFlattenedSearchEngineDimensions();

        $restricted = $this->requestFlattenedSearchEngines([
            'showColumns'                      => 'label,nb_visits',
            'keep_flattened_dimension_columns' => 1,
        ]);
        $row = $restricted->getFirstRow();

        foreach ($dimensions as $dimension) {
            $this->assertNotFalse($row->getColumn($dimension), $dimension . ' column should be kept');
        }

        $this->assertEquals(1, $row->getColumn('nb_visits'));
        $this->assertFalse($row->getColumn('nb_actions'), 'a non-allowlisted metric should still be removed');
    }

    public function testShowColumnsDropsFlattenedDimensionColumnsByDefault()
    {
        // showColumns is a public API parameter, so an allowlist must not be widened unless the
        // caller opts in: without the flag the dimension columns are dropped like any other column.
        $this->trackGoalConversion();

        $dimensions = $this->getFlattenedSearchEngineDimensions();

        $restricted = $this->requestFlattenedSearchEngines(['showColumns' => 'label,nb_visits']);
        $row        = $restricted->getFirstRow();

        foreach ($dimensions as $dimension) {
            $this->assertFalse($row->getColumn($dimension), $dimension . ' column should be dropped');
        }

        $this->assertEquals(1, $row->getColumn('nb_visits'));
    }

    /**
     * The search engines report is flattened into search engine + keyword, ie, two dimensions, which
     * is what makes the flattener add them as columns. Asserts the premise of the tests above.
     */
    private function getFlattenedSearchEngineDimensions(): array
    {
        $unrestricted = $this->requestFlattenedSearchEngines([]);
        $dimensions   = $unrestricted->getMetadata('dimensions');

        $this->assertIsArray($dimensions);
        $this->assertGreaterThan(1, count($dimensions), 'flattening should add a column per dimension');

        $row = $unrestricted->getFirstRow();
        foreach ($dimensions as $dimension) {
            $this->assertNotFalse($row->getColumn($dimension), $dimension . ' should be a column');
        }
        $this->assertNotFalse($row->getColumn('nb_actions'));

        return $dimensions;
    }

    private function requestFlattenedSearchEngines(array $extraParams): DataTable
    {
        return $this->requestReport('Referrers.getSearchEngines', $extraParams + [
            'flat'            => 1,
            'show_dimensions' => 1,
        ]);
    }

    private function requestReferrerTypes(array $extraParams): DataTable
    {
        return $this->requestReport('Referrers.getReferrerType', $extraParams);
    }

    private function requestReport(string $method, array $extraParams): DataTable
    {
        return Request::processRequest($method, array_merge([
            'idSite'       => $this->idSite,
            'period'       => 'day',
            'date'         => $this->dateTime,
            'format'       => 'original',
            'filter_limit' => -1,
        ], $extraParams));
    }
}
