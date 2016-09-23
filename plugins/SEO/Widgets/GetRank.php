<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO\Widgets;

use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\Widget\WidgetConfig;
use Piwik\Site;
use Piwik\Url;
use Piwik\UrlHelper;
use Piwik\Plugins\SEO\API;

class GetRank extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('SEO');
        $config->setName('SEO_SeoRankings');
    }

    public function render()
    {
        $idSite = Common::getRequestVar('idSite');
        $site = new Site($idSite);

        $url = urldecode(Common::getRequestVar('url', '', 'string'));

        if (!empty($url) && strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        if (empty($url) || !UrlHelper::isLookLikeUrl($url)) {
            $url = $site->getMainUrl();
        }

        $dataTable = API::getInstance()->getRank($url);
        
        /** @var \Piwik\DataTable\Renderer\Php $renderer */
        $renderer = Renderer::factory('php');
        $renderer->setSerialize(false);

        return $this->renderTemplate('getRank', array(
            'urlToRank' => Url::getHostFromUrl($url),
            'ranks' => $renderer->render($dataTable)
        ));
    }

}
