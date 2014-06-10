<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;

use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
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
    function __construct($plugins, $theme)
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
        if($this->catalog == null)
            $this->createCatalog();

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
        foreach ($this->fileLocations as $fileLocation) {

            $newUIAsset = new OnDiskUIAsset($this->getBaseDirectory(), $fileLocation);
            $this->catalog->addUIAsset($newUIAsset);
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
        return PIWIK_USER_PATH;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }
}
