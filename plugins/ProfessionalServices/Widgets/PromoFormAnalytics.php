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

class PromoFormAnalytics extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoForms');
        $config->setSubcategoryId('ProfessionalServices_PromoOverview');
        $config->setName(Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Form Analytics'));
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check('FormAnalytics');
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');

        $view->title = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', 'Form Analytics');
        $view->helpUrl = 'https://matomo.org/'; // where should this go if anywhere? It's required for the rating.
        $view->imageName = 'ad-formanalytics.png';
        $view->imageAlt = $view->title; // adjust if needed per plugin
        $view->listOfFeatures = [
            "Understand how users engage with your forms to find areas of improvement.",
            "Identify common user errors and validation issues within your forms, to enhance user satisfaction and conversion rates.",
            "Gain deeper insights into how different user groups interact with your forms and tailor your strategies accordingly.",
        ];

        return $view->render();
    }
}
