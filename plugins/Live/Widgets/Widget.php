<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\Widgets;

use Piwik\Common;
use Piwik\Plugins\Live\Live;
use Piwik\Widget\WidgetConfig;

class Widget extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Visitors');
        $config->setSubcategoryId('General_RealTime');
        $config->setName('Live_VisitorsInRealTime');
        $config->setIsWide();
        $config->setOrder(20);

        $idSite = Common::getRequestVar('idSite', 0, 'int');

        if (empty($idSite)) {
            return;
        }

        if (!Live::isVisitorLogEnabled($idSite)) {
            $config->disable();
        }
    }
}
