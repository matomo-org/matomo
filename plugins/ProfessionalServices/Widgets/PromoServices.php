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
use Piwik\Plugins\ProfessionalServices\Promo;
use Piwik\ProfessionalServices\Advertising;
use Piwik\View;
use Piwik\Widget\WidgetConfig;

class PromoServices extends \Piwik\Widget\Widget
{
    /**
     * @var Advertising
     */
    private $advertising;

    /**
     * @var Promo
     */
    private $promo;

    public function __construct(Advertising $advertising, Promo $promo)
    {
        $this->advertising = $advertising;
        $this->promo = $promo;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('ProfessionalServices_WidgetPremiumServicesForPiwik');

        $advertising = StaticContainer::get('Piwik\ProfessionalServices\Advertising');
        $config->setIsEnabled($advertising->areAdsForProfessionalServicesEnabled());
    }

    public function render()
    {
        $view = new View('@ProfessionalServices/promoServicesWidget');

        $promo = $this->promo->getContent();

        $view->ctaLinkUrl = $promo['url'];
        $view->ctaText = $promo['text'];
        $view->ctaTitle = $promo['title'];
        $view->ctaLinkTitle = $this->promo->getLinkTitle();

        return $view->render();
    }
}
