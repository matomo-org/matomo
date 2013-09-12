<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;
use Piwik\Http;

/**
 *
 * @package CorePluginsAdmin
 */
class MarketplaceApiClient extends \Piwik\Controller\Admin
{

    private function fetch($method, $params)
    {
        $endpoint = 'http://plugins.piwik.org/api/1.0/';
        $query    = http_build_query($params);

        $url = sprintf('%s%s?%s', $endpoint, $method, $query);

        $result = Http::sendHttpRequest($url, 5);
        $result = json_decode($result);

        return $result;
    }

    public function searchForPlugins($keywords, $query, $sort)
    {
        $response = $this->fetch('plugins', array('keywords' => $keywords, 'query' => $query, 'sort' => $sort));

        if (!empty($response->plugins)) {
            return $response->plugins;
        }

        return array();
    }

    public function searchForThemes($keywords, $query, $sort)
    {
        $response = $this->fetch('themes', array('keywords' => $keywords, 'query' => $query, 'sort' => $sort));

        if (!empty($response->plugins)) {
            return $response->plugins;
        }

        return array();
    }

}
