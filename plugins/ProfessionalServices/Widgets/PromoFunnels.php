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

class PromoFunnels extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoFunnels');
        $config->setSubcategoryId('ProfessionalServices_PromoOverview');
        $config->setName(Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Funnels'));
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check('Funnels');
        $isEnabled = true; // MK
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');

        $view->title = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Funnels');
        $view->helpUrl = 'https://matomo.org/'; // where should this go if anywhere? It's required for the rating.
        $view->imageName = 'ad-funnels.png';
        $view->imageAlt = $view->title; // adjust if needed per plugin
        $view->listOfFeatures = [
            "Identify and address drop-off points to improve conversion rates.",
            "Analyse user behaviour based on various criteria, and get valuable insights into how different user groups interact with your site.",
            "Make informed decisions by identifying optimisation opportunities, enhancing user engagement, and driving revenue growth through a deeper understanding of user journeys.",
        ];

        return $view->render();
    }
}
