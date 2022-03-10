<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Exception;
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
        $defaults = $this->getDefaultPluginInformation();
        $plugin   = $this->loadPluginInfoJson();

        // use translated plugin description if available
        if ($defaults['description'] != Piwik::translate($defaults['description'])) {
            unset($plugin['description']);
        }

        // look for a license file
        $licenseFile = $this->getPathToLicenseFile();
        if(!empty($licenseFile)) {
            $plugin['license_file'] = $licenseFile;
        }

        return array_merge(
            $defaults,
            $plugin
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
            'description'      => $descriptionKey,
            'homepage'         => 'https://matomo.org/',
            'authors'          => array(array('name' => 'Matomo', 'homepage'  => 'https://matomo.org/')),
            'license'          => 'GPL v3+',
            'version'          => Version::VERSION,
            'theme'            => false,
            'require'          => array()
        );
    }

    /**
     * It is important that this method works without using anything from DI
     * @return array|mixed
     */
    public function loadPluginInfoJson()
    {
        $path = $this->getPathToPluginJson();
        return $this->loadJsonMetadata($path);
    }

    public function getPathToPluginJson()
    {
        $path = $this->getPathToPluginFolder() . '/' . self::PLUGIN_JSON_FILENAME;
        return $path;
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

        $info = json_decode($json, $assoc = true);
        if (!is_array($info)
            || empty($info)
        ) {
            throw new Exception("Invalid JSON file: $path");
        }

        return $info;
    }

    /**
     * @return string
     */
    private function getPathToPluginFolder()
    {
        return \Piwik\Plugin\Manager::getPluginDirectory($this->pluginName);
    }

    /**
     * @return null|string
     */
    public function getPathToLicenseFile()
    {
        $prefixPath = $this->getPathToPluginFolder() . '/';
        $licenseFiles = array(
            'LICENSE',
            'LICENSE.md',
            'LICENSE.txt'
        );
        foreach ($licenseFiles as $licenseFile) {
            $pathToLicense = $prefixPath . $licenseFile;
            if (is_file($pathToLicense) && is_readable($pathToLicense)) {
                return $pathToLicense;
            }
        }
        return null;
    }
}
