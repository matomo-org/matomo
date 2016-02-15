<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PiwikPro\Widgets;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\PiwikPro\Advertising;
use Piwik\Plugins\PiwikPro\Promo;
use Piwik\View;
use Piwik\Widget\WidgetConfig;

class PromoPiwikPro extends \Piwik\Widget\Widget
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
        $config->setCategoryId('About Piwik');
        $config->setName('PiwikPro_WidgetPiwikProAd');
        $config->setIsEnabled(StaticContainer::get('Piwik\PiwikPro\Advertising')->arePiwikProAdsEnabled());
    }

    public function render()
    {
        $view = new View('@PiwikPro/promoPiwikProWidget');

        $promo = $this->promo->getContent();

        $view->ctaLinkUrl = $this->advertising->getPromoUrlForOnPremises('PromoWidget', $promo['campaignContent']);
        $view->ctaText = $promo['text'];
        $view->ctaLinkTitle = $this->promo->getLinkTitle();

        return $view->render();
    }
}
