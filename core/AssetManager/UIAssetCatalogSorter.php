<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;

class UIAssetCatalogSorter
{
    /**
     * @var string[]
     */
    private $priorityOrder;

    /**
     * @param string[] $priorityOrder
     */
    function __construct($priorityOrder)
    {
        $this->priorityOrder = $priorityOrder;
    }

    /**
     * @param UIAssetCatalog $uiAssetCatalog
     * @return UIAssetCatalog
     */
    public function sortUIAssetCatalog($uiAssetCatalog)
    {
        $sortedCatalog = new UIAssetCatalog($this);
        foreach ($this->priorityOrder as $filePattern) {

            $assetsMatchingPattern = array_filter($uiAssetCatalog->getAssets(), function($uiAsset) use ($filePattern)  {
                return preg_match('~^' . $filePattern . '~', $uiAsset->getRelativeLocation());
            });

            foreach($assetsMatchingPattern as $assetMatchingPattern) {
                $sortedCatalog->addUIAsset($assetMatchingPattern);
            }
        }

        $this->addUnmatchedAssets($uiAssetCatalog, $sortedCatalog);

        return $sortedCatalog;
    }

    /**
     * @param UIAssetCatalog $uiAssetCatalog
     * @param UIAssetCatalog $sortedCatalog
     */
    private function addUnmatchedAssets($uiAssetCatalog, $sortedCatalog)
    {
        foreach ($uiAssetCatalog->getAssets() as $uiAsset) {
            $sortedCatalog->addUIAsset($uiAsset);
        }
    }
}
