<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @method static \Piwik\AssetManager\UIAssetCacheBuster getInstance()
 * @package Piwik
 */
namespace Piwik\AssetManager;

use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\Singleton;
use Piwik\Version;

class UIAssetCacheBuster extends Singleton
{
    /**
     * Cache buster based on
     *  - Piwik version
     *  - Loaded plugins
     *  - Super user salt
     *  - Latest
     *
     * @param string[] $pluginNames
     * @return string
     */
    public function piwikVersionBasedCacheBuster($pluginNames = false)
    {
        $currentGitHash = @file_get_contents(PIWIK_INCLUDE_PATH . '/.git/refs/heads/master');
        $pluginList = md5(implode(",", !$pluginNames ? Manager::getInstance()->getLoadedPluginsName() : $pluginNames));
        $cacheBuster = md5(SettingsPiwik::getSalt() . $pluginList . PHP_VERSION . Version::VERSION . trim($currentGitHash));
        return $cacheBuster;
    }

    /**
     * @param string $content
     * @return string
     */
    public function md5BasedCacheBuster($content)
    {
        return md5($content);
    }
}
