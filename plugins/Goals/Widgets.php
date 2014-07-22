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
use Piwik\Site;
use Piwik\Piwik;

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

        $site = new Site($idSite);
        if ($site->isEcommerceEnabled()) {
            $this->addWidgetWithCustomCategory('Goals_Ecommerce', 'Goals_EcommerceOverview', 'widgetGoalReport', array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
            $this->addWidgetWithCustomCategory('Goals_Ecommerce', 'Goals_EcommerceLog', 'getEcommerceLog');
        }
    }

    private function getIdSite()
    {
        return Common::getRequestVar('idSite', null, 'int');
    }

}
