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
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class PromoFunnels extends Widget
{
    private const PROMO_PLUGIN_NAME = 'Funnels';
    private const PROMO_PLUGIN_NAME_NICE = 'Funnels';

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoFunnels');
        $config->setSubcategoryId('ProfessionalServices_PromoOverview');
        $config->setName(Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', self::PROMO_PLUGIN_NAME_NICE));
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check(self::PROMO_PLUGIN_NAME);
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/pluginAdvertising');

        $view->title = Piwik::translate('ProfessionalServices_PromoUnlockPowerOf', self::PROMO_PLUGIN_NAME_NICE);
        $view->pluginName = self::PROMO_PLUGIN_NAME;
        $view->pluginNameNice = self::PROMO_PLUGIN_NAME_NICE;
        $view->imageName = 'ad-funnels.png';
        $view->listOfFeatures = [
            "Identify and address drop-off points to improve conversion rates.",
            "Analyse user behaviour based on various criteria, and get valuable insights into how different user groups interact with your site.",
            "Make informed decisions by identifying optimisation opportunities, enhancing user engagement, and driving revenue growth through a deeper understanding of user journeys.",
        ];

        $view->installNonce = Nonce::getNonce(\Piwik\Plugins\Marketplace\Controller::INSTALL_NONCE);

        return $view->render();
    }
}
