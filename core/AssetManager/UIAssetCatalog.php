<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;

class UIAssetCatalog
{
    /**
     * @var UIAsset[]
     */
    private $uiAssets = array();

    /**
     * @var UIAssetCatalogSorter
     */
    private $catalogSorter;

    /**
     * @var string[]  Absolute file locations
     */
    private $existingAssetLocations = array();

    /**
     * @param UIAssetCatalogSorter $catalogSorter
     */
    public function __construct($catalogSorter)
    {
        $this->catalogSorter = $catalogSorter;
    }

    /**
     * @param UIAsset $uiAsset
     */
    public function addUIAsset($uiAsset)
    {
        $location = $uiAsset->getAbsoluteLocation();

        if (!$this->assetAlreadyInCatalog($location)) {
            $this->existingAssetLocations[] = $location;
            $this->uiAssets[] = $uiAsset;
        }
    }

    /**
     * @return UIAsset[]
     */
    public function getAssets()
    {
        return $this->uiAssets;
    }

    /**
     * @return UIAssetCatalog
     */
    public function getSortedCatalog()
    {
        return $this->catalogSorter->sortUIAssetCatalog($this);
    }

    /**
     * @param UIAsset $uiAsset
     * @return boolean
     */
    private function assetAlreadyInCatalog($location)
    {
        return in_array($location, $this->existingAssetLocations);
    }
}
