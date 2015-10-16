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
use Piwik\Menu\MenuUser;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Translate;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $idSite = $this->getIdSite();
        $goals  = API::getInstance()->getGoals($idSite);
        $mainGoalMenu = 'Goals_Goals';

        if (count($goals) == 0) {
            $linkToAddNewGoal = $this->urlForAction('addNewGoal', array(
                'idGoal' => null,
            ));
            $menu->addItem($mainGoalMenu, '', array(), 25);
            $menu->addItem($mainGoalMenu, 'Goals_AddNewGoal', $linkToAddNewGoal, 1);
            return;
        }

        $order = 1;

        $url = $this->urlForAction('index', array('idGoal' => null));

        $menu->addItem($mainGoalMenu, '', $url, 25);
        $menu->addItem($mainGoalMenu, 'General_Overview', $url, ++$order);

        $group = new Group();
        foreach ($goals as $goal) {
            $subMenuName = str_replace('%', '%%', Translate::clean($goal['name']));
            $params      = $this->urlForAction('goalReport', array('idGoal' => $goal['idgoal']));
            $tooltip     = sprintf('%s (id = %d)', $subMenuName, $goal['idgoal']);

            if (count($goals) > 3) {
                $group->add($subMenuName, $params, $tooltip);
            } else {
                $menu->addItem($mainGoalMenu, $subMenuName, $params, ++$order, $tooltip);
            }
        }

        if (count($goals) > 3) {
            $menu->addGroup($mainGoalMenu, 'Goals_ChooseGoal', $group, ++$order, $tooltip = false);
        }

        $menu->addItem($mainGoalMenu, 'Goals_ManageGoals', $this->urlForAction('editGoals'), ++$order);
    }

    public function configureUserMenu(MenuUser $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $this->getIdSite($userPreferences->getDefaultWebsiteId());

        if (Piwik::isUserHasAdminAccess($idSite)) {
            $menu->addManageItem('Goals_Goals', $this->urlForAction('manage', array('idSite' => $idSite)), 1);
        }

    }

    private function getIdSite($default = null)
    {
        $idSite = Common::getRequestVar('idSite', $default, 'int');
        return $idSite;
    }

}
