<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Measurable\Type\TypeManager;

class Menu extends \Piwik\Plugin\Menu
{
    private $typeManager;

    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addMeasurableItem('General_Settings', $this->urlForAction('globalSettings'), $order = 11);
        }
        
        if (Piwik::isUserHasSomeAdminAccess() && SitesManager::isSitesAdminEnabled()) {
            $menu->addMeasurableItem('SitesManager_MenuManage', $this->urlForAction('index'), $order = 10);

            $type = $this->getFirstTypeIfOnlyOneIsInUse();

            if ($type) {
                $menu->rename('CoreAdminHome_MenuMeasurables', $subMenuOriginal = null, $type->getNamePlural(), $subMenuRenamed = null);
            }
        }
    }

    private function getFirstTypeIfOnlyOneIsInUse()
    {
        $types = $this->typeManager->getAllTypes();

        if (count($types) === 1) {
            // only one type is in use, use this one for the wording
            return reset($types);

        } else {
            // multiple types are activated, check whether only one is actually in use
            $model   = new Model();
            $typeIds = $model->getUsedTypeIds();

            if (count($typeIds) === 1) {
                $typeManager = new TypeManager();
                return $typeManager->getType(reset($typeIds));
            }
        }
    }
}
