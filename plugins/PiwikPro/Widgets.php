<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PiwikPro;

use Piwik\Piwik;
use Piwik\PiwikPro\Advertising;
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
        if ($this->advertising->arePiwikProAdsEnabled()) {
            $this->addWidget('PiwikPro_WidgetBlogTitle', 'rssPiwikPro');
            $this->addWidget('PiwikPro_WidgetPiwikProAd', 'promoPiwikPro');
        }
    }

    public function rssPiwikPro()
    {
        try {
            $rss = new RssRenderer('https://piwik.pro/feed/');
            $rss->showDescription(true);

            return $rss->get();

        } catch (\Exception $e) {

            return $this->error($e);
        }
    }

    public function promoPiwikPro()
    {
        $view = new View('@PiwikPro/promoPiwikProWidget');

        $promo = $this->promo->getContent();

        $view->ctaLinkUrl = $this->advertising->getPromoUrlForOnPremises('PromoWidget', $promo['campaignContent']);
        $view->ctaText = $promo['text'];
        $view->ctaLinkTitle = $this->promo->getLinkTitle();

        return $view->render();
    }

    /**
     * @param \Exception $e
     * @return string
     */
    private function error($e)
    {
        return '<div class="pk-emptyDataTable">'
             . Piwik::translate('General_ErrorRequest', array('', ''))
             . ' - ' . $e->getMessage() . '</div>';
    }

}
