<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\WidgetsList;
use Piwik\Common;
use Piwik\Site;
use Piwik\Piwik;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $site  = new Site($idSite);
        if ($site->isEcommerceEnabled()) {
            $this->addEcommerceWidgets($widgetsList);
        }

        $this->addGoalsWidgets($widgetsList, $idSite);
    }

    private function addEcommerceWidgets(WidgetsList $widgetsList)
    {
        $goals = new Goals();

        $widgetsList->add('Goals_Ecommerce', 'Goals_EcommerceOverview', 'Goals', 'widgetGoalReport', array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
        $widgetsList->add('Goals_Ecommerce', 'Goals_EcommerceLog', 'Goals', 'getEcommerceLog');
        foreach ($goals->getEcommerceReports() as $widget) {
            $widgetsList->add('Goals_Ecommerce', $widget[0], $widget[1], $widget[2]);
        }
    }

    private function addGoalsWidgets(WidgetsList $widgetsList, $idSite)
    {
        $widgetsList->add('Goals_Goals', 'Goals_GoalsOverview', 'Goals', 'widgetGoalsOverview');

        $goals = API::getInstance()->getGoals($idSite);
        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                $widgetsList->add('Goals_Goals', Common::sanitizeInputValue($goal['name']), 'Goals', 'widgetGoalReport', array('idGoal' => $goal['idgoal']));
            }
        }
    }

}
