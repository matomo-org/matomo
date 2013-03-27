<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SEO
 */

/**
 * @package Piwik_SEO
 */
class Piwik_SEO_Controller extends Piwik_Controller
{
    function getRank()
    {
        $idSite = Piwik_Common::getRequestVar('idSite');
        $site = new Piwik_Site($idSite);

        $url = urldecode(Piwik_Common::getRequestVar('url', '', 'string'));

        if (!empty($url) && strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        if (empty($url) || !Piwik_Common::isLookLikeUrl($url)) {
            $url = $site->getMainUrl();
        }

        $dataTable = Piwik_SEO_API::getInstance()->getRank($url);

        $view = Piwik_View::factory('index');
        $view->urlToRank = Piwik_SEO_RankChecker::extractDomainFromUrl($url);

        $renderer = Piwik_DataTable_Renderer::factory('php');
        $renderer->setSerialize(false);
        $view->ranks = $renderer->render($dataTable);
        echo $view->render();
    }
}
