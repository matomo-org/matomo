<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\Widgets;

use Piwik\Widget\WidgetConfig;

class ManageSegments extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $idSite = \Piwik\Request::fromRequest()->getIntegerParameter('idSite', 0);

        $config->setCategoryId('General_Visitors');
        $config->setSubcategoryId('CoreHome_Segments');
        $config->setName('CoreHome_Segments');
        $config->setIsNotWidgetizable();

        if (empty($idSite)) {
            $config->disable();
        }
    }
}
