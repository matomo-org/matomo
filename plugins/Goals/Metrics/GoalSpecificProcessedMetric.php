<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics;

use Piwik\DataTable\Row;
use Piwik\Plugin\ProcessedMetric;

/**
 * Base class for processed metrics that are calculated using metrics that are
 * specific to certain goals.
 */
abstract class GoalSpecificProcessedMetric extends ProcessedMetric
{
    /**
     * The ID of the goal to calculate metrics for.
     *
     * @var int
     */
    protected $idGoal;

    /**
     * Constructor.
     *
     * @param int $idGoal The ID of the goal to calculate metrics for.
     */
    public function __construct($idGoal)
    {
        $this->idGoal = $idGoal;
    }

    protected function getColumnPrefix()
    {
        return 'goal_' . $this->idGoal;
    }

    protected function getGoalMetrics(Row $row)
    {
        $allGoalMetrics = $this->getMetric($row, 'goals');
        return @$allGoalMetrics[$this->idGoal] ?: array();
    }
}