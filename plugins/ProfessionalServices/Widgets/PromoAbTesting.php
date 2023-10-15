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

class PromoAbTesting extends Widget
{
    private const PROMO_PLUGIN_NAME = 'AbTesting';
    private const PROMO_PLUGIN_NAME_NICE = 'A/B Tests';

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoAbTesting');
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
        $view->imageName = 'ad-abtests.png';
        $view->listOfFeatures = [
            "Stop guessing, start using data, and compare webpage versions to increase conversions.",
            "Compare various elements, including layout, design, copy, and more, while monitoring performance in real-time.",
            "Identify and address user behaviour patterns, so you can increase conversion rates and achieve better results from your digital efforts.",
        ];

        $view->installNonce = Nonce::getNonce(\Piwik\Plugins\Marketplace\Controller::INSTALL_NONCE);

        return $view->render();
    }
}
