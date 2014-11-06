<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Tracker\GoalManager;

/**
 * TODO
 */
abstract class GoalSpecificProcessedMetric extends ProcessedMetric
{
    /**
     * TODO
     */
    protected $idGoal;

    /**
     * TODO
     */
    public function __construct($idGoal)
    {
        $this->idGoal = $idGoal;
    }

    protected function getColumnPrefix()
    {
        return 'goal_' . $this->idGoal;
    }
}