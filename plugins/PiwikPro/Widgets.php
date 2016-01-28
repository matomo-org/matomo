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

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'About Piwik';

    /**
     * @var Advertising
     */
    private $advertising;

    public function __construct(Advertising $advertising)
    {
        $this->advertising = $advertising;
    }

    protected function init()
    {
        if ($this->advertising->arePiwikProAdsEnabled()) {
            $this->addWidget('PiwikPro_WidgetBlogTitle', 'rssPiwikPro');
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
