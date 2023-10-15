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

class PromoCrashAnalytics extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoCrashAnalytics');
        $config->setSubcategoryId('ProfessionalServices_PromoOverview');
        $config->setName(Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Crash Analytics'));
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check('CrashAnalytics');
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');

        $view->title = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Crash Analytics');
        $view->helpUrl = 'https://matomo.org/'; // where should this go if anywhere? It's required for the rating.
        $view->imageName = 'ad-crashanalytics.png';
        $view->imageAlt = $view->title; // adjust if needed per plugin
        $view->listOfFeatures = [
            "Automatically track website crashes (e.g. broken carts, unresponsive forms, etc.) for swift bug resolution, so you can ensure a seamless and bug-free user experience.",
            "Resolve crashes efficiently with detailed insights into bug locations, users’ interactions and users’ device information.",
            "Elevate your websiteʼs performance with real-time crash alerts and scheduled reports to stay informed and ready to resolve bugs quickly.",
        ];

        return $view->render();
    }
}
