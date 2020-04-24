<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager\UIAssetMerger;

use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager\UIAssetCacheBuster;
use Piwik\AssetManager\UIAssetFetcher\JScriptUIAssetFetcher;
use Piwik\AssetManager\UIAssetMerger;
use Piwik\AssetManager\UIAssetMinifier;
use Piwik\Piwik;

class JScriptUIAssetMerger extends UIAssetMerger
{
    /**
     * @var UIAssetMinifier
     */
    private $assetMinifier;

    /**
     * @param UIAsset $mergedAsset
     * @param JScriptUIAssetFetcher $assetFetcher
     * @param UIAssetCacheBuster $cacheBuster
     */
    public function __construct($mergedAsset, $assetFetcher, $cacheBuster)
    {
        parent::__construct($mergedAsset, $assetFetcher, $cacheBuster);

        $this->assetMinifier = UIAssetMinifier::getInstance();
    }

    protected function getMergedAssets()
    {
        return $this->getConcatenatedAssets();
    }

    protected function generateCacheBuster()
    {
        $cacheBuster = $this->cacheBuster->piwikVersionBasedCacheBuster($this->getPlugins());
        return "/* Matomo Javascript - cb=" . $cacheBuster . "*/\n";
    }

    protected function getPreamble()
    {
        return $this->getCacheBusterValue();
    }

    protected function postEvent(&$mergedContent)
    {
        $plugins = $this->getPlugins();

        if (!empty($plugins)) {

            /**
             * Triggered after all the JavaScript files Piwik uses are minified and merged into a
             * single file, but before the merged JavaScript is written to disk.
             *
             * Plugins can use this event to modify merged JavaScript or do something else
             * with it.
             *
             * @param string $mergedContent The minified and merged JavaScript.
             */
            Piwik::postEvent('AssetManager.filterMergedJavaScripts', array(&$mergedContent), null, $plugins);
        }
    }

    public function getFileSeparator()
    {
        return "\n";
    }

    protected function processFileContent($uiAsset)
    {
        $content = $uiAsset->getContent();

        if (!$this->assetMinifier->isMinifiedJs($content)) {
            $content = $this->assetMinifier->minifyJs($content);
        }

        return $content;
    }
}
