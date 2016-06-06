<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\Columns\Metrics;

use Piwik\Common;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\Goals\API as GoalsAPI;
use Piwik\Tracker\GoalManager;

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
     * The ID of the site the goal belongs to.
     *
     * @var int
     */
    protected $idSite;

    /**
     * Constructor.
     *
     * @param int|null $idSite The ID of the site the goal belongs to. If supplied, affects the formatting
     *                         and translated name of the metric.
     * @param int $idGoal The ID of the goal to calculate metrics for.
     */
    public function __construct($idSite, $idGoal)
    {
        $this->idSite = $idSite;
        $this->idGoal = $idGoal;
    }

    protected function getColumnPrefix()
    {
        return 'goal_' . $this->idGoal;
    }

    protected function getGoalMetrics(Row $row)
    {
        $allGoalMetrics = $this->getMetric($row, 'goals');
        if (isset($allGoalMetrics[$this->idGoal])) {
            return $allGoalMetrics[$this->idGoal];
        } else {
            $alternateKey = 'idgoal=' . $this->idGoal;
            if (isset($allGoalMetrics[$alternateKey])) {
                return $allGoalMetrics[$alternateKey];
            } elseif ($this->idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
                $alternateKey = GoalManager::IDGOAL_ORDER;
                if (isset($allGoalMetrics[$alternateKey])) {
                    return $allGoalMetrics[$alternateKey];
                }
            } elseif ($this->idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                $alternateKey = GoalManager::IDGOAL_CART;
                if (isset($allGoalMetrics[$alternateKey])) {
                    return $allGoalMetrics[$alternateKey];
                }
            } else {
                return array();
            }
        }
    }

    protected function getGoalName()
    {
        if ($this->idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            return Piwik::translate('Goals_EcommerceOrder');
        }

        if (isset($this->idSite)) {
            $allGoals = GoalsAPI::getInstance()->getGoals($this->idSite);
            $goalName = @$allGoals[$this->idGoal]['name'];
            return Common::sanitizeInputValue($goalName);
        } else {
            return "";
        }
    }

    protected function getGoalNameForDocs()
    {
        $goalName = $this->getGoalName();
        if ($goalName == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            $goalName = '"' . $goalName . '"';
        }
        return $goalName;
    }
}