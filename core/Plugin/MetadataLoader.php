<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
use Piwik\Common;
use Piwik\PluginsManager;

/**
 * @see core/Version.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Version.php';

/**
 * Loads plugin metadata found in the following files:
 * - plugin.piwik.json
 * - colors.piwik.json
 */
class Piwik_Plugin_MetadataLoader
{
    const PLUGIN_JSON_FILENAME = 'plugin.piwik.json';
    const COLORS_JSON_FILENAME = 'colors.piwik.json';
    
    const SHORT_COLOR_LENGTH = 4;
    const LONG_COLOR_LENGTH = 7;
    
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
     * Loads plugin metadata. @see Piwik_Plugin::getInformation.
     * 
     * @return array
     */
    public function load()
    {
        return array_merge(
            $this->getDefaultPluginInformation(),
            $this->loadPluginInfoJson(),
            $this->loadPluginColorsJson()
        );
    }
    
    private function getDefaultPluginInformation()
    {
        $descriptionKey = $this->pluginName . '_PluginDescription';
        return array(
            'description'      => Piwik_Translate($descriptionKey),
            'homepage'         => 'http://piwik.org/',
            'author'           => 'Piwik',
            'author_homepage'  => 'http://piwik.org/',
            'license'          => 'GPL v3 or later',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html',
            'version'          => Piwik_Version::VERSION,
            'theme'            => false,
        );
    }
    
    private function loadPluginInfoJson()
    {
        $path = PluginsManager::getPluginsDirectory() . $this->pluginName . '/' . self::PLUGIN_JSON_FILENAME;
        return $this->loadJsonMetadata($path);
    }
    
    private function loadPluginColorsJson()
    {
        $path = PluginsManager::getPluginsDirectory() . $this->pluginName . '/' . self::COLORS_JSON_FILENAME;
        $info = $this->loadJsonMetadata($path);
        $info = $this->cleanAndValidatePluginColorsJson($path, $info);
        return $info;
    }
    
    private function cleanAndValidatePluginColorsJson($path, $info)
    {
        // check that if "colors" exists, it is an array
        $colors = isset($info["colors"]) ? $info["colors"] : array();
        if (!is_array($colors)) {
            throw new Exception("The 'colors' value in '$path' must be an object mapping names with colors.");
        }
        
        // validate each color
        foreach ($colors as $color) {
            if (!$this->isStringColorValid($color)) {
                throw new Exception("Invalid color string '$color' in '$path'.");
            }
        }
        
        return array("colors" => $colors); // make sure only 'colors' element is loaded
    }
    
    private function isStringColorValid($color)
    {
        if (strlen($color) !== self::SHORT_COLOR_LENGTH
            && strlen($color) !== self::LONG_COLOR_LENGTH
        ) {
            return false;
        }
        
        if ($color[0] !== '#') {
            return false;
        }
        
        return ctype_xdigit(substr($color, 1)); // check if other digits are hex
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
