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

class PromoMediaAnalytics extends Widget
{
    private const PROMO_PLUGIN_NAME = 'MediaAnalytics';
    private const PROMO_PLUGIN_NAME_NICE = 'Media Analytics';

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoMedia');
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
        $view->imageName = 'ad-mediaanalytics.png';
        $view->listOfFeatures = [
            "Get detailed insights into user engagement with audio and video content, to help you understand what resonates with your audience.",
            "Start fine-tuning your content strategy right away, no complex configurations required.",
            "See who, how much, and which parts of your media visitors have consumed and which content contributes the most value to your business.",
        ];

        $view->installNonce = Nonce::getNonce(\Piwik\Plugins\Marketplace\Controller::INSTALL_NONCE);

        return $view->render();
    }
}
