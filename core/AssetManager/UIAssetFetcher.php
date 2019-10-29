<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\Plugin\Manager;
use Piwik\Theme;

abstract class UIAssetFetcher
{
    /**
     * @var UIAssetCatalog
     */
    protected $catalog;

    /**
     * @var string[]
     */
    protected $fileLocations = array();

    /**
     * @var string[]
     */
    protected $plugins;

    /**
     * @var Theme
     */
    private $theme;

    /**
     * @param string[] $plugins
     * @param Theme $theme
     */
    public function __construct($plugins, $theme)
    {
        $this->plugins = $plugins;
        $this->theme = $theme;
    }

    /**
     * @return string[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * $return UIAssetCatalog
     */
    public function getCatalog()
    {
        if ($this->catalog == null) {
            $this->createCatalog();
        }

        return $this->catalog;
    }

    abstract protected function retrieveFileLocations();

    /**
     * @return string[]
     */
    abstract protected function getPriorityOrder();

    private function createCatalog()
    {
        $this->retrieveFileLocations();

        $this->initCatalog();

        $this->populateCatalog();

        $this->sortCatalog();
    }

    private function initCatalog()
    {
        $catalogSorter = new UIAssetCatalogSorter($this->getPriorityOrder());
        $this->catalog = new UIAssetCatalog($catalogSorter);
    }

    private function populateCatalog()
    {
        $pluginBaseDir = Manager::getPluginsDirectory();
        $pluginWebDirectories = Manager::getAlternativeWebRootDirectories();
        $matomoRootDir = $this->getBaseDirectory();

        foreach ($this->fileLocations as $fileLocation) {
            $fileAbsolute = $matomoRootDir . '/' . $fileLocation;

            $newUIAsset = new OnDiskUIAsset($this->getBaseDirectory(), $fileLocation);
            if ($newUIAsset->exists()) {
                $this->catalog->addUIAsset($newUIAsset);
                continue;
            }

            $found = false;

            if (strpos($fileAbsolute, $pluginBaseDir) === 0) {
                // we iterate over all custom plugin directories only for plugin files, not libs files (not needed there)
                foreach ($pluginWebDirectories as $pluginDirectory => $relative) {
                    $fileTest = str_replace($pluginBaseDir, $pluginDirectory, $fileAbsolute);
                    $newFileRelative = str_replace($pluginDirectory, '', $fileTest);
                    $testAsset = new OnDiskUIAsset($pluginDirectory, $newFileRelative, $relative);
                    if ($testAsset->exists()) {
                        $this->catalog->addUIAsset($testAsset);
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                // we add it anyway so it'll trigger an error about the missing file
                $this->catalog->addUIAsset($newUIAsset);
            }
        }
    }

    private function sortCatalog()
    {
        $this->catalog = $this->catalog->getSortedCatalog();
    }

    /**
     * @return string
     */
    private function getBaseDirectory()
    {
        // served by web server directly, so must be a public path
        return PIWIK_DOCUMENT_ROOT;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }
}
