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
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class PromoCustomReports extends Widget
{
    private const PROMO_PLUGIN_NAME = 'CustomReports';

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoCustomReports');
        $config->setSubcategoryId('ProfessionalServices_PromoManage');
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check(self::PROMO_PLUGIN_NAME);
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $marketplacePlugins = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins');
        $pluginInfo = $marketplacePlugins->getPluginInfo(self::PROMO_PLUGIN_NAME);

        $view = new View('@ProfessionalServices/pluginAdvertising');
        $view->plugin = $pluginInfo;

        $view->title  = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', $pluginInfo['displayName']);
        $view->listOfFeatures = [
            "Create analytics reports customised to your specific business goals and KPIs, ensuring they focus on the most relevant data.",
            "Drill down into specific data for deeper insights into user behaviour and engagement.",
            "Save time and resources by automating report generation, enabling real-time monitoring, and providing cost-effective analytics solutions without the need for third-party tools.",
        ];

        return $view->render();
    }
}
