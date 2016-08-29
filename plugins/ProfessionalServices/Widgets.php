<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Piwik;
use Piwik\ProfessionalServices\Advertising;
use Piwik\Plugins\ExampleRssWidget\RssRenderer;
use Piwik\View;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'About Piwik';

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

    protected function init()
    {
        if ($this->advertising->areAdsForProfessionalServicesEnabled()) {
            $this->addWidget('ProfessionalServices_WidgetProfessionalServicesForPiwik', 'promoServices');
        }
    }

    public function rss()
    {
        return '';
    }

    public function promoServices()
    {
        $view = new View('@ProfessionalServices/promoServicesWidget');

        $promo = $this->promo->getContent();

        $view->ctaLinkUrl = $this->advertising->getPromoUrlForPiwikProUpgrade();
        $view->ctaText = $promo['text'];
        $view->ctaLinkTitle = $this->promo->getLinkTitle();

        return $view->render();
    }
}
