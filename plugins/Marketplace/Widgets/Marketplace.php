<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace\Widgets;

use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class Marketplace extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Marketplace_Marketplace');
        $config->setSubcategoryId('Marketplace_Browse');
        $config->setName(Piwik::translate('Marketplace_Marketplace'));
        $config->setModule('Marketplace');
        $config->setAction('overview');
        $config->setParameters(array('embed' => '1'));
        $config->setIsNotWidgetizable();
        $config->setOrder(19);
        $config->setIsEnabled(!Piwik::isUserIsAnonymous());
    }


}