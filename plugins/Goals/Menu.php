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
use Piwik\Menu\MenuReporting;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Translate;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureReportingMenu(MenuReporting $menu)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals  = API::getInstance()->getGoals($idSite);
        $mainGoalMenu = $this->getGoalCategoryName($idSite);

        $site = new Site($idSite);

        if (count($goals) == 0) {

            $menu->add($mainGoalMenu, '', array('module' => 'Goals',
                    'action' => ($site->isEcommerceEnabled() ? 'ecommerceReport' : 'addNewGoal'),
                    'idGoal' => ($site->isEcommerceEnabled() ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER : null)),
                true,
                25);

            if ($site->isEcommerceEnabled()) {
                $menu->add($mainGoalMenu, 'Goals_Ecommerce', array('module' => 'Goals', 'action' => 'ecommerceReport', 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER), true, 1);
            }

            $menu->add($mainGoalMenu, 'Goals_AddNewGoal', array('module' => 'Goals', 'action' => 'addNewGoal'));

        } else {

            $menu->add($mainGoalMenu, '', array('module' => 'Goals',
                    'action' => ($site->isEcommerceEnabled() ? 'ecommerceReport' : 'index'),
                    'idGoal' => ($site->isEcommerceEnabled() ? Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER : null)),
                true,
                25);

            if ($site->isEcommerceEnabled()) {
                $menu->add($mainGoalMenu, 'Goals_Ecommerce', array('module' => 'Goals', 'action' => 'ecommerceReport', 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER), true, 1);
            }

            $menu->add($mainGoalMenu, 'Goals_GoalsOverview', array('module' => 'Goals', 'action' => 'index'), true, 2);

            foreach ($goals as $goal) {
                $menu->add($mainGoalMenu, str_replace('%', '%%', Translate::clean($goal['name'])), array('module' => 'Goals', 'action' => 'goalReport', 'idGoal' => $goal['idgoal']));
            }
        }
    }

    private function getGoalCategoryName($idSite)
    {
        $site = new Site($idSite);
        return $site->isEcommerceEnabled() ? 'Goals_EcommerceAndGoalsMenu' : 'Goals_Goals';
    }
}
