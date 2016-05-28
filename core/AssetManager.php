<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager\UIAsset\InMemoryUIAsset;
use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAssetCacheBuster;
use Piwik\AssetManager\UIAssetFetcher\JScriptUIAssetFetcher;
use Piwik\AssetManager\UIAssetFetcher\StaticUIAssetFetcher;
use Piwik\AssetManager\UIAssetFetcher\StylesheetUIAssetFetcher;
use Piwik\AssetManager\UIAssetFetcher;
use Piwik\AssetManager\UIAssetMerger\JScriptUIAssetMerger;
use Piwik\AssetManager\UIAssetMerger\StylesheetUIAssetMerger;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;

/**
 * AssetManager is the class used to manage the inclusion of UI assets:
 * JavaScript and CSS files.
 *
 * It performs the following actions:
 *  - Identifies required assets
 *  - Includes assets in the rendered HTML page
 *  - Manages asset merging and minifying
 *  - Manages server-side cache
 *
 * Whether assets are included individually or as merged files is defined by
 * the global option 'disable_merged_assets'. See the documentation in the global
 * config for more information.
 *
 * @method static AssetManager getInstance()
 */
class AssetManager extends Singleton
{
    const MERGED_CSS_FILE = "asset_manager_global_css.css";
    const MERGED_CORE_JS_FILE = "asset_manager_core_js.js";
    const MERGED_NON_CORE_JS_FILE = "asset_manager_non_core_js.js";

    const CSS_IMPORT_DIRECTIVE = "<link rel=\"stylesheet\" type=\"text/css\" href=\"%s\" />\n";
    const JS_IMPORT_DIRECTIVE = "<script type=\"text/javascript\" src=\"%s\"></script>\n";
    const GET_CSS_MODULE_ACTION = "index.php?module=Proxy&action=getCss";
    const GET_CORE_JS_MODULE_ACTION = "index.php?module=Proxy&action=getCoreJs";
    const GET_NON_CORE_JS_MODULE_ACTION = "index.php?module=Proxy&action=getNonCoreJs";

    /**
     * @var UIAssetCacheBuster
     */
    private $cacheBuster;

    /**
     * @var UIAssetFetcher
     */
    private $minimalStylesheetFetcher;

    /**
     * @var Theme
     */
    private $theme;

    public function __construct()
    {
        $this->cacheBuster = UIAssetCacheBuster::getInstance();
        $this->minimalStylesheetFetcher =  new StaticUIAssetFetcher(array('plugins/Morpheus/stylesheets/base.less', 'plugins/Morpheus/stylesheets/general/_forms.less'), array(), $this->theme);

        $theme = Manager::getInstance()->getThemeEnabled();
        if (!empty($theme)) {
            $this->theme = new Theme();
        }
    }

    /**
     * @param UIAssetCacheBuster $cacheBuster
     */
    public function setCacheBuster($cacheBuster)
    {
        $this->cacheBuster = $cacheBuster;
    }

    /**
     * @param UIAssetFetcher $minimalStylesheetFetcher
     */
    public function setMinimalStylesheetFetcher($minimalStylesheetFetcher)
    {
        $this->minimalStylesheetFetcher = $minimalStylesheetFetcher;
    }

    /**
     * @param Theme $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Return CSS file inclusion directive(s) using the markup <link>
     *
     * @return string
     */
    public function getCssInclusionDirective()
    {
        return sprintf(self::CSS_IMPORT_DIRECTIVE, self::GET_CSS_MODULE_ACTION);
    }

    /**
     * Return JS file inclusion directive(s) using the markup <script>
     *
     * @return string
     */
    public function getJsInclusionDirective()
    {
        $result = "<script type=\"text/javascript\">\n" . Translate::getJavascriptTranslations() . "\n</script>";

        if ($this->isMergedAssetsDisabled()) {
            $this->getMergedCoreJSAsset()->delete();
            $this->getMergedNonCoreJSAsset()->delete();

            $result .= $this->getIndividualCoreAndNonCoreJsIncludes();
        } else {
            $result .= sprintf(self::JS_IMPORT_DIRECTIVE, self::GET_CORE_JS_MODULE_ACTION);
            $result .= sprintf(self::JS_IMPORT_DIRECTIVE, self::GET_NON_CORE_JS_MODULE_ACTION);
        }

        return $result;
    }

    /**
     * Return the base.less compiled to css
     *
     * @return UIAsset
     */
    public function getCompiledBaseCss()
    {
        $mergedAsset = new InMemoryUIAsset();

        $assetMerger = new StylesheetUIAssetMerger($mergedAsset, $this->minimalStylesheetFetcher, $this->cacheBuster);

        $assetMerger->generateFile();

        return $mergedAsset;
    }

    /**
     * Return the css merged file absolute location.
     * If there is none, the generation process will be triggered.
     *
     * @return UIAsset
     */
    public function getMergedStylesheet()
    {
        $mergedAsset = $this->getMergedStylesheetAsset();

        $assetFetcher = new StylesheetUIAssetFetcher(Manager::getInstance()->getLoadedPluginsName(), $this->theme);

        $assetMerger = new StylesheetUIAssetMerger($mergedAsset, $assetFetcher, $this->cacheBuster);

        $assetMerger->generateFile();

        return $mergedAsset;
    }

    /**
     * Return the core js merged file absolute location.
     * If there is none, the generation process will be triggered.
     *
     * @return UIAsset
     */
    public function getMergedCoreJavaScript()
    {
        return $this->getMergedJavascript($this->getCoreJScriptFetcher(), $this->getMergedCoreJSAsset());
    }

    /**
     * Return the non core js merged file absolute location.
     * If there is none, the generation process will be triggered.
     *
     * @return UIAsset
     */
    public function getMergedNonCoreJavaScript()
    {
        return $this->getMergedJavascript($this->getNonCoreJScriptFetcher(), $this->getMergedNonCoreJSAsset());
    }

    /**
     * @param boolean $core
     * @return string[]
     */
    public function getLoadedPlugins($core)
    {
        $loadedPlugins = array();

        foreach (Manager::getInstance()->getPluginsLoadedAndActivated() as $plugin) {
            $pluginName = $plugin->getPluginName();
            $pluginIsCore = Manager::getInstance()->isPluginBundledWithCore($pluginName);

            if (($pluginIsCore && $core) || (!$pluginIsCore && !$core)) {
                $loadedPlugins[] = $pluginName;
            }
        }

        return $loadedPlugins;
    }

    /**
     * Remove previous merged assets
     */
    public function removeMergedAssets($pluginName = false)
    {
        $assetsToRemove = array($this->getMergedStylesheetAsset());

        if ($pluginName) {
            if ($this->pluginContainsJScriptAssets($pluginName)) {
                if (Manager::getInstance()->isPluginBundledWithCore($pluginName)) {
                    $assetsToRemove[] = $this->getMergedCoreJSAsset();
                } else {
                    $assetsToRemove[] = $this->getMergedNonCoreJSAsset();
                }
            }
        } else {
            $assetsToRemove[] = $this->getMergedCoreJSAsset();
            $assetsToRemove[] = $this->getMergedNonCoreJSAsset();
        }

        $this->removeAssets($assetsToRemove);
    }

    /**
     * Check if the merged file directory exists and is writable.
     *
     * @return string The directory location
     * @throws Exception if directory is not writable.
     */
    public function getAssetDirectory()
    {
        $mergedFileDirectory = StaticContainer::get('path.tmp') . '/assets';

        if (!is_dir($mergedFileDirectory)) {
            Filesystem::mkdir($mergedFileDirectory);
        }

        if (!is_writable($mergedFileDirectory)) {
            throw new Exception("Directory " . $mergedFileDirectory . " has to be writable.");
        }

        return $mergedFileDirectory;
    }

    /**
     * Return the global option disable_merged_assets
     *
     * @return boolean
     */
    public function isMergedAssetsDisabled()
    {
        if (Config::getInstance()->Development['disable_merged_assets'] == 1) {
            return true;
        }
        
        if (isset($_GET['disable_merged_assets']) && $_GET['disable_merged_assets'] == 1) {
            return true;
        }
        
        return false;
    }

    /**
     * @param UIAssetFetcher $assetFetcher
     * @param UIAsset $mergedAsset
     * @return UIAsset
     */
    private function getMergedJavascript($assetFetcher, $mergedAsset)
    {
        $assetMerger = new JScriptUIAssetMerger($mergedAsset, $assetFetcher, $this->cacheBuster);

        $assetMerger->generateFile();

        return $mergedAsset;
    }

    /**
     * Return individual JS file inclusion directive(s) using the markup <script>
     *
     * @return string
     */
    private function getIndividualCoreAndNonCoreJsIncludes()
    {
        return
            $this->getIndividualJsIncludesFromAssetFetcher($this->getCoreJScriptFetcher()) .
            $this->getIndividualJsIncludesFromAssetFetcher($this->getNonCoreJScriptFetcher());
    }

    /**
     * @param UIAssetFetcher $assetFetcher
     * @return string
     */
    private function getIndividualJsIncludesFromAssetFetcher($assetFetcher)
    {
        $jsIncludeString = '';

        $assets = $assetFetcher->getCatalog()->getAssets();

        foreach ($assets as $jsFile) {
            $jsFile->validateFile();
            $jsIncludeString = $jsIncludeString . sprintf(self::JS_IMPORT_DIRECTIVE, $jsFile->getRelativeLocation());
        }

        return $jsIncludeString;
    }

    private function getCoreJScriptFetcher()
    {
        return new JScriptUIAssetFetcher($this->getLoadedPlugins(true), $this->theme);
    }

    private function getNonCoreJScriptFetcher()
    {
        return new JScriptUIAssetFetcher($this->getLoadedPlugins(false), $this->theme);
    }

    /**
     * @param string $pluginName
     * @return boolean
     */
    private function pluginContainsJScriptAssets($pluginName)
    {
        $fetcher = new JScriptUIAssetFetcher(array($pluginName), $this->theme);

        try {
            $assets = $fetcher->getCatalog()->getAssets();
        } catch (\Exception $e) {
            // This can happen when a plugin is not valid (eg. Piwik 1.x format)
            // When posting the event to the plugin, it returns an exception "Plugin has not been loaded"
            return false;
        }

        $pluginManager = Manager::getInstance();
        $plugin = $pluginManager->getLoadedPlugin($pluginName);

        if ($plugin->isTheme()) {
            $theme = $pluginManager->getTheme($pluginName);

            $javaScriptFiles = $theme->getJavaScriptFiles();

            if (!empty($javaScriptFiles)) {
                $assets = array_merge($assets, $javaScriptFiles);
            }
        }

        return !empty($assets);
    }

    /**
     * @param UIAsset[] $uiAssets
     */
    public function removeAssets($uiAssets)
    {
        foreach ($uiAssets as $uiAsset) {
            $uiAsset->delete();
        }
    }

    /**
     * @return UIAsset
     */
    public function getMergedStylesheetAsset()
    {
        return $this->getMergedUIAsset(self::MERGED_CSS_FILE);
    }

    /**
     * @return UIAsset
     */
    private function getMergedCoreJSAsset()
    {
        return $this->getMergedUIAsset(self::MERGED_CORE_JS_FILE);
    }

    /**
     * @return UIAsset
     */
    private function getMergedNonCoreJSAsset()
    {
        return $this->getMergedUIAsset(self::MERGED_NON_CORE_JS_FILE);
    }

    /**
     * @param string $fileName
     * @return UIAsset
     */
    private function getMergedUIAsset($fileName)
    {
        return new OnDiskUIAsset($this->getAssetDirectory(), $fileName);
    }
}
