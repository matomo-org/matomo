<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SEO;

use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\Site;
use Piwik\Url;
use Piwik\UrlHelper;
use Piwik\View;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'SEO';

    public function init()
    {
        $this->addWidget('SEO_SeoRankings', 'getRank');
    }

    public function getRank()
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

        $view = new View('@SEO/getRank');
        $view->urlToRank = Url::getHostFromUrl($url);

        /** @var \Piwik\DataTable\Renderer\Php $renderer */
        $renderer = Renderer::factory('php');
        $renderer->setSerialize(false);
        $view->ranks = $renderer->render($dataTable);

        return $view->render();
    }

}
