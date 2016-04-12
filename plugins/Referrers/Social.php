<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;
use Piwik\Cache;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Singleton;

/**
 * Contains methods to access search engine definition data.
 */
class Social extends Singleton
{
    const OPTION_STORAGE_NAME = 'SocialDefinitions';

    /** @var string location of definition file (relative to PIWIK_INCLUDE_PATH) */
    const DEFINITION_FILE = '/vendor/piwik/searchengine-and-social-list/Socials.yml';

    protected $definitionList = null;

    /**
     * Returns list of search engines by URL
     *
     * @return array  Array of ( URL => array( searchEngineName, keywordParameter, path, charset ) )
     */
    public function getDefinitions()
    {
        $cache = Cache::getEagerCache();
        $cacheId = 'Social-' . self::OPTION_STORAGE_NAME;

        if ($cache->contains($cacheId)) {
            $list = $cache->fetch($cacheId);
        } else {
            $list = $this->loadDefinitions();
            $cache->save($cacheId, $list);
        }

        return $list;
    }

    private function loadDefinitions()
    {
        if ($this->definitionList === null) {
            // Read first from the auto-updated list in database
            $list = Option::get(self::OPTION_STORAGE_NAME);

            if ($list) {
                $this->definitionList = unserialize(base64_decode($list));
            } else {
                // Fallback to reading the bundled list
                $yml = file_get_contents(PIWIK_INCLUDE_PATH . self::DEFINITION_FILE);
                $this->definitionList = $this->loadYmlData($yml);
                Option::set(self::OPTION_STORAGE_NAME, base64_encode(serialize($this->definitionList)));
            }
        }

        Piwik::postEvent('Referrer.addSocialUrls', array(&$this->definitionList));

        return $this->definitionList;
    }

    /**
     * Parses the given YML string and caches the resulting definitions
     *
     * @param string $yml
     * @return array
     */
    public function loadYmlData($yml)
    {
        $searchEngines = \Spyc::YAMLLoadString($yml);

        $this->definitionList = $this->transformData($searchEngines);

        return $this->definitionList;
    }

    protected function transformData($socials)
    {
        $urlToName = array();
        foreach ($socials as $name => $urls) {
            if (empty($urls) || !is_array($urls)) {
                continue;
            }

            foreach ($urls as $url) {
                $urlToName[$url] = $name;
            }
        }
        return $urlToName;
    }

    /**
     * Returns true if a URL belongs to a social network, false if otherwise.
     *
     * @param string $url The URL to check.
     * @param string|bool $socialName The social network's name to check for, or false to check
     *                                 for any.
     * @return bool
     */
    public function isSocialUrl($url, $socialName = false)
    {
        foreach ($this->getDefinitions() as $domain => $name) {

            if (preg_match('/(^|[\.\/])'.$domain.'([\.\/]|$)/', $url) && ($socialName === false || $name == $socialName)) {

                return true;
            }
        }

        return false;
    }


    /**
     * Get's social network name from URL.
     *
     * @param string $url
     * @return string
     */
    public function getSocialNetworkFromDomain($url)
    {
        foreach ($this->getDefinitions() as $domain => $name) {

            if (preg_match('/(^|[\.\/])'.$domain.'([\.\/]|$)/', $url)) {

                return $name;
            }
        }

        return Piwik::translate('General_Unknown');
    }

    /**
     * Returns the main url of the social network the given url matches
     *
     * @param string  $url
     *
     * @return string
     */
    public function getMainUrl($url)
    {
        $social  = $this->getSocialNetworkFromDomain($url);
        foreach ($this->getDefinitions() as $domain => $name) {

            if ($name == $social) {

                return $domain;
            }
        }
        return $url;
    }


    /**
     * Return social network logo path by URL
     *
     * @param string $domain
     * @return string path
     * @see plugins/Referrers/images/socials/
     */
    public function getLogoFromUrl($domain)
    {
        $social = $this->getSocialNetworkFromDomain($domain);
        $socialNetworks = $this->getDefinitions();

        $filePattern = 'plugins/Referrers/images/socials/%s.png';

        $socialDomains = array_keys($socialNetworks, $social);
        foreach ($socialDomains as $domain) {
            if (file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($filePattern, $domain))) {
                return sprintf($filePattern, $domain);
            }
        }

        return sprintf($filePattern, 'xx');
    }
}
