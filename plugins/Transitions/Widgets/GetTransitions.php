<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Transitions\Widgets;

use Piwik\Piwik;
use Piwik\Request;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetTransitions extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Actions');
        $config->setSubcategoryId('Transitions_Transitions');
        $config->setName('Transitions_Transitions');
        $config->setOrder(99);
        $config->setClientSideComponent('Transitions', 'TransitionsPage');
        $idSite = self::getIdSite();
        if (!$idSite || !Piwik::isUserHasViewAccess($idSite)) {
            $config->disable();
        }
    }

    private static function getIdSite()
    {
        return Request::fromRequest()->getIntegerParameter('idSite', 0);
    }
}
