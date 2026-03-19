<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Request;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $idSite = Request::fromRequest()->getIntegerParameter('idSite', -1);
        if ($idSite === -1) {
            $idSite = $this->getDefaultIdSiteForUser();
        }
        if ($idSite !== -1) {
            $menu->addMeasurableItem(
                'CoreHome_Segments',
                $this->urlForModuleAction('CoreHome', 'index', [
                    'idSite' => $idSite,
                    'category' => 'General_Visitors',
                    'subcategory' => 'CoreHome_Segments',
                ]),
                19,
                Piwik::translate('SegmentEditor_ManageSegments'),
                'icon-outlink'
            );
        }
    }

    private function getDefaultIdSiteForUser(): int
    {
        $sites = SitesManagerAPI::getInstance()->getSitesWithAtLeastViewAccess(1);
        $site = reset($sites);
        if (!empty($site['idsite'])) {
            return (int) $site['idsite'];
        }

        return -1;
    }
}
