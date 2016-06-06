<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\Common;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Goals_Goals';

    protected function init()
    {
        $this->addWidget('Goals_GoalsOverview', 'widgetGoalsOverview');

        $idSite = $this->getIdSite();
        $goals  = API::getInstance()->getGoals($idSite);

        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                $name   = Common::sanitizeInputValue($goal['name']);
                $params = array('idGoal' => $goal['idgoal']);

                $this->addWidget($name, 'widgetGoalReport', $params);
            }
        }
    }

    private function getIdSite()
    {
        return Common::getRequestVar('idSite', null, 'int');
    }

}
