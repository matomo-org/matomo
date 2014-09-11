<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager;

use Piwik\AssetManager\PiwikLessCompiler;
use Piwik\AssetManager\UIAsset\StylesheetUIAsset;
use Piwik\AssetManager;

abstract class UIAssetMerger
{
    /**
     * @var UIAssetFetcher
     */
    private $assetFetcher;

    /**
     * @var UIAsset
     */
    private $mergedAsset;

    /**
     * @var string
     */
    private $mergedContent;

    /**
     * @var UIAssetCacheBuster
     */
    protected $cacheBuster;

    /**
     * @param UIAsset $mergedAsset
     * @param UIAssetFetcher $assetFetcher
     * @param UIAssetCacheBuster $cacheBuster
     */
    function __construct($mergedAsset, $assetFetcher, $cacheBuster)
    {
        $this->mergedAsset = $mergedAsset;
        $this->assetFetcher = $assetFetcher;
        $this->cacheBuster = $cacheBuster;
    }

    public function generateFile()
    {
        if(!$this->shouldGenerate())
            return;

        $this->mergedContent = $this->getMergedAssets();

        $this->postEvent($this->mergedContent);

        $this->adjustPaths();

        $this->addPreamble();

        $this->writeContentToFile();
    }

    /**
     * @return string
     */
    abstract protected function getMergedAssets();

    /**
     * @return string
     */
    abstract protected function generateCacheBuster();

    /**
     * @return string
     */
    abstract protected function getPreamble();

    /**
     * @return string
     */
    abstract protected function getFileSeparator();

    /**
     * @param UIAsset $uiAsset
     * @return string
     */
    abstract protected function processFileContent($uiAsset);

    /**
     * @param string $mergedContent
     */
    abstract protected function postEvent(&$mergedContent);

    protected function getConcatenatedAssets()
    {
        if (empty($this->mergedContent)) {
            $this->concatenateAssets();
        }

        return $this->mergedContent;
    }

    private function concatenateAssets()
    {
        $mergedContent = '';

        foreach ($this->getAssetCatalog()->getAssets() as $uiAsset) {

            $uiAsset->validateFile();
            $content = $this->processFileContent($uiAsset);

            $mergedContent .= $this->getFileSeparator() . $content;
        }

        $this->mergedContent = $mergedContent;
    }

    /**
     * @return string[]
     */
    protected function getPlugins()
    {
        return $this->assetFetcher->getPlugins();
    }

    /**
     * @return UIAssetCatalog
     */
    protected function getAssetCatalog()
    {
        return $this->assetFetcher->getCatalog();
    }

    /**
     * @return boolean
     */
    private function shouldGenerate()
    {
        if(!$this->mergedAsset->exists())
            return true;

        return !$this->isFileUpToDate();
    }

    /**
     * @return boolean
     */
    private function isFileUpToDate()
    {
        $f = fopen($this->mergedAsset->getAbsoluteLocation(), 'r');
        $firstLine = fgets($f);
        fclose($f);

        if (!empty($firstLine) && trim($firstLine) == trim($this->getCacheBusterValue())) {
            return true;
        }

        // Some CSS file in the merge, has changed since last merged asset was generated
        // Note: we do not detect changes in @import'ed LESS files
        return false;
    }

    /**
     * @return boolean
     */
    private function isMergedAssetsDisabled()
    {
        return AssetManager::getInstance()->isMergedAssetsDisabled();
    }

    private function adjustPaths()
    {
        $theme = $this->assetFetcher->getTheme();
        // During installation theme is not yet ready
        if($theme) {
            $this->mergedContent = $this->assetFetcher->getTheme()->rewriteAssetsPathToTheme($this->mergedContent);
        }
    }

    private function writeContentToFile()
    {
        $this->mergedAsset->writeContent($this->mergedContent);
    }

    /**
     * @return string
     */
    protected function getCacheBusterValue()
    {
        if(empty($this->cacheBusterValue))
            $this->cacheBusterValue = $this->generateCacheBuster();

        return $this->cacheBusterValue;
    }

    private function addPreamble()
    {
        $this->mergedContent = $this->getPreamble() . $this->mergedContent;
    }

    /**
     * @return boolean
     */
    private function shouldCompareExistingVersion()
    {
        return $this->isMergedAssetsDisabled();
    }
}
