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

class PromoAbTesting extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoAbTesting');
        $config->setSubcategoryId('ProfessionalServices_PromoOverview');
        $config->setName(Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'A/B Tests'));
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check('AbTesting');
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');

        $view->title = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'A/B Tests');
        $view->helpUrl = 'https://matomo.org/'; // where should this go if anywhere? It's required for the rating.
        $view->imageName = 'ad-abtests.png';
        $view->imageAlt = $view->title; // adjust if needed per plugin
        $view->listOfFeatures = [
            "Stop guessing, start using data, and compare webpage versions to increase conversions.",
            "Compare various elements, including layout, design, copy, and more, while monitoring performance in real-time.",
            "Identify and address user behaviour patterns, so you can increase conversion rates and achieve better results from your digital efforts.",
        ];

        return $view->render();
    }
}
