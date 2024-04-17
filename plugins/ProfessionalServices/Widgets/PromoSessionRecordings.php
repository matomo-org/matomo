<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ProfessionalServices\Widgets;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Widget\WidgetConfig;

class PromoSessionRecordings extends DismissibleWidget
{
    private const PROMO_PLUGIN_NAME = 'HeatmapSessionRecording';

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoSessionRecording');
        $config->setSubcategoryId('ProfessionalServices_PromoManage');
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check(self::PROMO_PLUGIN_NAME, self::getDismissibleWidgetName());
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $marketplacePlugins = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins');
        $pluginInfo = $marketplacePlugins->getPluginInfo(self::PROMO_PLUGIN_NAME);

        $view = new View('@ProfessionalServices/pluginAdvertising');
        $view->plugin = $pluginInfo;
        $view->widgetName = self::getDismissibleWidgetName();
        $view->userCanDismiss = Piwik::isUserIsAnonymous() === false;

        $view->title  = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Session Recordings'); // custom title
        $view->imageName = 'ad-sessionrecordings.png';
        $view->listOfFeatures = [
            Piwik::translate('ProfessionalServices_SessionRecordingsFeature01'),
            Piwik::translate('ProfessionalServices_SessionRecordingsFeature02'),
            Piwik::translate('ProfessionalServices_SessionRecordingsFeature03'),
        ];

        return $view->render();
    }
}
