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
use Piwik\Menu\Group;
use Piwik\Menu\MenuReporting;
use Piwik\Piwik;
use Piwik\Translate;

class Menu extends \Piwik\Plugin\Menu
{

    public function configureReportingMenu(MenuReporting $menu)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals  = API::getInstance()->getGoals($idSite);
        $mainGoalMenu = 'Goals_Goals';

        if (count($goals) == 0) {
            $action = 'addNewGoal';
            $url    = $this->urlForAction($action, array(
                'idGoal' => null
            ));

            $menu->addItem($mainGoalMenu, '', $url, 25);
            $menu->addItem($mainGoalMenu, 'Goals_AddNewGoal', $this->urlForAction('addNewGoal'));

        } else {

            $action = 'index';
            $url    = $this->urlForAction($action, array('idGoal' => null));

            $menu->addItem($mainGoalMenu, '', $url, 25);
            $menu->addItem($mainGoalMenu, 'Goals_GoalsOverview', array('module' => 'Goals', 'action' => 'index'), 2);

            $group = new Group();
            foreach ($goals as $goal) {
                $subMenuName = str_replace('%', '%%', Translate::clean($goal['name']));
                $params      = $this->urlForAction('goalReport', array('idGoal' => $goal['idgoal']));
                $tooltip     = sprintf('%s (id = %d)', $subMenuName, $goal['idgoal']);

                if (count($goals) <= 3) {
                    $menu->addItem($mainGoalMenu, $subMenuName, $params, 50, $tooltip);
                } else {
                    $group->add($subMenuName, $params, $tooltip);
                }
            }

            if (count($goals) > 3) {
                $menu->addGroup($mainGoalMenu, 'Goals_ChooseGoal', $group, $orderId = 50, $tooltip = false);
            }
        }

        if (0 !== count($goals) && Piwik::isUserHasAdminAccess($idSite)) {
            $menu->addItem($mainGoalMenu, 'Goals_GoalsManagement', $this->urlForAction('manage'), 1);
        }

    }

}
