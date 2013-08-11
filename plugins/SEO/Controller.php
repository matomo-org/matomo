<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package SEO
 */
namespace Piwik\Plugins\SEO;

use Piwik\Common;
use Piwik\DataTable\Renderer;
use Piwik\Plugins\SEO\API;
use Piwik\View;
use Piwik\Site;
use Piwik\Plugins\SEO\RankChecker;

/**
 * @package SEO
 */
class Controller extends \Piwik\Controller
{
    function getRank()
    {
        $idSite = Common::getRequestVar('idSite');
        $site = new Site($idSite);

        $url = urldecode(Common::getRequestVar('url', '', 'string'));

        if (!empty($url) && strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        if (empty($url) || !Common::isLookLikeUrl($url)) {
            $url = $site->getMainUrl();
        }

        $dataTable = API::getInstance()->getRank($url);

        $view = new View('@SEO/getRank');
        $view->urlToRank = RankChecker::extractDomainFromUrl($url);

        /** @var \Piwik\DataTable\Renderer\Php $renderer */
        $renderer = Renderer::factory('php');
        $renderer->setSerialize(false);
        $view->ranks = $renderer->render($dataTable);
        echo $view->render();
    }
}
