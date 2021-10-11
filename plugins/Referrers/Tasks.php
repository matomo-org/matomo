<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;


use Piwik\Config;
use Piwik\Http;
use Piwik\Option;
use Piwik\SettingsPiwik;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        if(SettingsPiwik::isInternetEnabled() === true &&
            Config::getInstance()->General['enable_referrer_definition_syncs'] == 1){
            $this->weekly('updateSearchEngines');
            $this->weekly('updateSocials');
        }
    }

    /**
     * Update the search engine definitions
     *
     * @see https://github.com/matomo-org/searchengine-and-social-list
     */
    public function updateSearchEngines()
    {
        $url = 'https://raw.githubusercontent.com/matomo-org/searchengine-and-social-list/master/SearchEngines.yml';
        $list = Http::sendHttpRequest($url, 30);
        $searchEngines = SearchEngine::getInstance()->loadYmlData($list);
        if (count($searchEngines) < 200) {
            return;
        }
        Option::set(SearchEngine::OPTION_STORAGE_NAME, base64_encode(serialize($searchEngines)));
    }

    /**
     * Update the social definitions
     *
     * @see https://github.com/matomo-org/searchengine-and-social-list
     */
    public function updateSocials()
    {
        $url = 'https://raw.githubusercontent.com/matomo-org/searchengine-and-social-list/master/Socials.yml';
        $list = Http::sendHttpRequest($url, 30);
        $socials = Social::getInstance()->loadYmlData($list);
        if (count($socials) < 50) {
            return;
        }
        Option::set(Social::OPTION_STORAGE_NAME, base64_encode(serialize($socials)));
    }
}
