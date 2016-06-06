<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
        if (Piwik::isUserHasSomeAdminAccess()) {
            $type = $this->getFirstTypeIfOnlyOneIsInUse();

            $menuName = 'General_Measurables';
            if ($type) {
                $menuName = $type->getNamePlural();
            }

            $menu->addManageItem($menuName,
                                 $this->urlForAction('index'),
                                 $order = 1);
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
