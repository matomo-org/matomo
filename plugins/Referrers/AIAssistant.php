<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Singleton;

/**
 * Contains methods to access AI assistant definition data.
 */
class AIAssistant extends Singleton
{
    public const OPTION_STORAGE_NAME = 'AIAssistantDefinitions';

    /** @var string location of definition file (relative to PIWIK_INCLUDE_PATH) */
    public const DEFINITION_FILE = '/vendor/matomo/searchengine-and-social-list/AIAssistants.yml';

    /** @var null|array<string, string> */
    protected $definitionList = null;

    /**
     * Returns list of AI assistants by URL
     *
     * @return array<string, string>
     */
    public function getDefinitions(): array
    {
        $cache = Cache::getEagerCache();
        $cacheId = 'AIAssistant-' . self::OPTION_STORAGE_NAME;

        if ($cache->contains($cacheId)) {
            /** @var array<string, string> $list */
            $list = $cache->fetch($cacheId);
        } else {
            $list = $this->loadDefinitions();
            $cache->save($cacheId, $list);
        }

        return $list;
    }

    /**
     * @return array<string, string>
     */
    private function loadDefinitions(): array
    {
        if ($this->definitionList === null) {
            $referrerDefinitionSyncOpt = Config::getInstance()->General['enable_referrer_definition_syncs'];

            if ($referrerDefinitionSyncOpt == 1) {
                $this->loadRemoteDefinitions();
            } else {
                $this->loadLocalYmlData();
            }
        }

        Piwik::postEvent('Referrer.addAIAssistantUrls', [&$this->definitionList]);

        return $this->definitionList ?? [];
    }

    /**
     * Loads definitions sourced from remote yaml with a local fallback
     */
    private function loadRemoteDefinitions(): void
    {
        // Read first from the auto-updated list in database
        $list = Option::get(self::OPTION_STORAGE_NAME);

        if ($list && SettingsPiwik::isInternetEnabled()) {
            $list = Common::safe_unserialize(base64_decode($list));
            if (!empty($list) && is_array($list)) {
                $this->definitionList = $list;
            }
        } else {
            // Fallback to reading the bundled list
            $this->loadLocalYmlData();
            Option::set(self::OPTION_STORAGE_NAME, base64_encode(serialize($this->definitionList)));
        }
    }

    /**
     * Loads the definition data from the local definitions file
     */
    private function loadLocalYmlData(): void
    {
        $yml = file_get_contents(PIWIK_INCLUDE_PATH . self::DEFINITION_FILE);
        if ($yml !== false) {
            $this->definitionList = $this->loadYmlData($yml);
        }
    }

    /**
     * Parses the given YML string and caches the resulting definitions
     *
     * @param string $yml
     * @return null|array<string, string>
     */
    public function loadYmlData(string $yml): ?array
    {
        $ais = \Spyc::YAMLLoadString($yml);

        if (is_array($ais)) {
            $this->definitionList = $this->transformData($ais);
        }

        return $this->definitionList;
    }

    /**
     * @param array<string, string[]> $ais
     * @return array<string, string>
     */
    protected function transformData(array $ais): array
    {
        $urlToName = [];
        foreach ($ais as $name => $urls) {
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
     * Returns true if a URL belongs to an AI assistant, false if otherwise.
     *
     * @param string $url The URL to check.
     * @param string|null $aiAssistantName The name of the AI assistant to check for, or false to check for any.
     * @return bool
     */
    public function isAIAssistantUrl(string $url, ?string $aiAssistantName = null): bool
    {
        foreach ($this->getDefinitions() as $domain => $name) {
            if (preg_match('#(^|[\.\/])' . preg_quote($domain) . '(\/|$)#', $url) && ($aiAssistantName === null || $name === $aiAssistantName)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Gets AI assistant name from URL.
     */
    public function getAIAssistantFromDomain(string $url): string
    {
        foreach ($this->getDefinitions() as $domain => $name) {
            if (preg_match('#(^|[\.\/])' . preg_quote($domain) . '(\/|$)#', $url)) {
                return $name;
            }
        }

        return Piwik::translate('General_Unknown');
    }

    /**
     * Returns the main url of the AI assistant the given url matches
     */
    public function getMainUrl(string $url): string
    {
        $ai  = $this->getAIAssistantFromDomain($url);
        foreach ($this->getDefinitions() as $domain => $name) {
            if ($name === $ai) {
                return $domain;
            }
        }
        return $url;
    }

    /**
     * Returns the main url of the given AI assistant
     */
    public function getMainUrlFromName(string $aiAssistant): ?string
    {
        foreach ($this->getDefinitions() as $domain => $name) {
            if ($name === $aiAssistant) {
                return $domain;
            }
        }
        return null;
    }


    /**
     * Return AI assistant logo path by URL
     *
     * @see plugins/Morpheus/icons/dist/aiAssistants/
     */
    public function getLogoFromUrl(string $url): string
    {
        $ai = $this->getAIAssistantFromDomain($url);
        $ais = $this->getDefinitions();

        $filePattern = 'plugins/Morpheus/icons/dist/aiAssistants/%s.png';

        $aiDomains = array_keys($ais, $ai);
        foreach ($aiDomains as $domain) {
            if (file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($filePattern, $domain))) {
                return sprintf($filePattern, $domain);
            }
        }

        return sprintf($filePattern, 'xx');
    }
}
