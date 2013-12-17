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
namespace Piwik\AssetManager\UIAssetMerger;

use Exception;
use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager\UIAssetMerger;
use Piwik\Piwik;
use lessc;

class StylesheetUIAssetMerger extends UIAssetMerger
{
    /**
     * @var lessc
     */
    private $lessCompiler;

    function __construct($mergedAsset, $assetFetcher, $cacheBuster)
    {
        parent::__construct($mergedAsset, $assetFetcher, $cacheBuster);

        $this->lessCompiler = self::getLessCompiler();
    }

    protected function getMergedAssets()
    {
        foreach($this->getAssetCatalog()->getAssets() as $uiAsset) {
            $this->lessCompiler->addImportDir(dirname($uiAsset->getAbsoluteLocation()));
        }

        return $this->lessCompiler->compile($this->getConcatenatedAssets());
    }

    /**
     * @return lessc
     * @throws Exception
     */
    private static function getLessCompiler()
    {
        if (!class_exists("lessc")) {
            throw new Exception("Less was added to composer during 2.0. ==> Execute this command to update composer packages: \$ php composer.phar install");
        }
        $less = new lessc();
        return $less;
    }

    protected function generateCacheBuster()
    {
        $fileHash = $this->cacheBuster->md5BasedCacheBuster($this->getConcatenatedAssets());
        return "/* compile_me_once=$fileHash */";
    }

    protected function getPreamble()
    {
        return $this->getCacheBusterValue() . "\n"
        . "/* Piwik CSS file is compiled with Less. You may be interested in writing a custom Theme for Piwik! */\n";
    }

    protected function postEvent(&$mergedContent)
    {
        /**
         * Triggered after all less stylesheets are compiled to CSS, minified and merged into
         * one file, but before the generated CSS is written to disk.
         *
         * This event can be used to modify merged CSS.
         *
         * @param string $mergedContent The merged and minified CSS.
         */
        Piwik::postEvent('AssetManager.filterMergedStylesheets', array(&$mergedContent));
    }

    public function getFileSeparator()
    {
        return '';
    }

    protected function processFileContent($uiAsset)
    {
        return $this->rewriteCssPathsDirectives($uiAsset);
    }

    /**
     * Rewrite css url directives
     * - rewrites paths defined relatively to their css/less definition file
     * - rewrite windows directory separator \\ to /
     *
     * @param UIAsset $uiAsset
     * @return string
     */
    private function rewriteCssPathsDirectives($uiAsset)
    {
        static $rootDirectoryLength = null;
        if (is_null($rootDirectoryLength)) {
            $rootDirectoryLength = self::countDirectoriesInPathToRoot($uiAsset);
        }

        $baseDirectory = dirname($uiAsset->getRelativeLocation());
        $content = preg_replace_callback(
            "/(url\(['\"]?)([^'\")]*)/",
            function ($matches) use ($rootDirectoryLength, $baseDirectory) {

                $absolutePath = realpath(PIWIK_USER_PATH . "/$baseDirectory/" . $matches[2]);

                if($absolutePath) {

                    $relativePath = substr($absolutePath, $rootDirectoryLength);

                    $relativePath = str_replace('\\', '/', $relativePath);

                    return $matches[1] . $relativePath;

                } else {
                    return $matches[1] . $matches[2];
                }
            },
            $uiAsset->getContent()
        );
        return $content;
    }

    /**
     * @param UIAsset $uiAsset
     * @return int
     */
    protected function countDirectoriesInPathToRoot($uiAsset)
    {
        $rootDirectory = realpath($uiAsset->getBaseDirectory());
        if ($rootDirectory != '/' && substr_compare($rootDirectory, '/', -1)) {
            $rootDirectory .= '/';
        }
        $rootDirectoryLen = strlen($rootDirectory);
        return $rootDirectoryLen;
    }
}
