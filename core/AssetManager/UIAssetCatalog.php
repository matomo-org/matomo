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
     * @param UIAssetCatalogSorter $catalogSorter
     */
    function __construct($catalogSorter)
    {
        $this->catalogSorter = $catalogSorter;
    }

    /**
     * @param UIAsset $uiAsset
     */
    public function addUIAsset($uiAsset)
    {
        if(!$this->assetAlreadyInCatalog($uiAsset)) {

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
    private function assetAlreadyInCatalog($uiAsset)
    {
        foreach($this->uiAssets as $existingAsset)
            if($uiAsset->getAbsoluteLocation() == $existingAsset->getAbsoluteLocation())
                return true;

        return false;
    }
}
