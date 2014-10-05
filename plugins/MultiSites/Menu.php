<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

use Piwik\Menu\MenuTop;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $urlParams = $this->urlForActionWithDefaultUserParams('index', array('segment' => false, 'idSite' => false));
        $tooltip   = Piwik::translate('MultiSites_TopLinkTooltip');

        $menu->add('General_MultiSitesSummary', null, $urlParams, true, 3, $tooltip);
    }
}
