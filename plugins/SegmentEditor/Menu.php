<?php

/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\Plugins\SegmentEditor;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Request;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $idSite = Request::fromRequest()->getIntegerParameter('idSite', 1);
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
