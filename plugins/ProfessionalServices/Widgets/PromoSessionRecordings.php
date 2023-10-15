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

class PromoSessionRecordings extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoSessionRecording');
        $config->setSubcategoryId('ProfessionalServices_PromoManage');
        $config->setName(Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Session Recordings'));
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check('HeatmapSessionRecording');
        $isEnabled = true; // MK
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');

        $view->title = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Session Recordings');
        $view->helpUrl = 'https://matomo.org/'; // where should this go if anywhere? It's required for the rating.
        $view->imageName = 'ad-sessionrecordings.png';
        $view->imageAlt = $view->title; // adjust if needed per plugin
        $view->listOfFeatures = [
            "See how visitors interact with your site in real-time and uncover valuable insights to improve user experience.",
            "Identify barriers and successful user journeys, leading to higher conversion rates.",
            "Gain in-depth insights into how users engage with specific content, forms, or elements, to tailor your content and design to better meet user preferences.",
        ];

        return $view->render();
    }
}
