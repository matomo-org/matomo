<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Exception;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Version;

/**
 * @see core/Version.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Version.php';

/**
 * Loads plugin metadata found in the following files:
 * - piwik.json
 */
class MetadataLoader
{
    const PLUGIN_JSON_FILENAME = 'plugin.json';

    /**
     * The name of the plugin whose metadata will be loaded.
     *
     * @var string
     */
    private $pluginName;

    /**
     * Constructor.
     *
     * @param string $pluginName Name of the plugin to load metadata.
     */
    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    /**
     * Loads plugin metadata. @see Plugin::getInformation.
     *
     * @return array
     */
    public function load()
    {
        return array_merge(
            $this->getDefaultPluginInformation(),
            $this->loadPluginInfoJson()
        );
    }

    public function hasPluginJson()
    {
        $hasJson = $this->loadPluginInfoJson();

        return !empty($hasJson);
    }

    private function getDefaultPluginInformation()
    {
        $descriptionKey = $this->pluginName . '_PluginDescription';
        return array(
            'description'      => Piwik::translate($descriptionKey),
            'homepage'         => 'http://piwik.org/',
            'authors'          => array(array('name' => 'Piwik', 'homepage'  => 'http://piwik.org/')),
            'license'          => 'GPL v3+',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html',
            'version'          => Version::VERSION,
            'theme'            => false,
            'require'          => array()
        );
    }

    private function loadPluginInfoJson()
    {
        $path = \Piwik\Plugin\Manager::getPluginsDirectory() . $this->pluginName . '/' . self::PLUGIN_JSON_FILENAME;
        return $this->loadJsonMetadata($path);
    }

    private function loadJsonMetadata($path)
    {
        if (!file_exists($path)) {
            return array();
        }

        $json = file_get_contents($path);
        if (!$json) {
            return array();
        }

        $info = Common::json_decode($json, $assoc = true);
        if (!is_array($info)
            || empty($info)
        ) {
            throw new Exception("Invalid JSON file: $path");
        }
        return $info;
    }
}
