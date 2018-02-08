<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager\UIAssetMerger;

use Exception;
use lessc;
use Piwik\AssetManager\UIAsset;
use Piwik\AssetManager\UIAssetMerger;
use Piwik\Common;
use Piwik\Exception\StylesheetLessCompileException;
use Piwik\Piwik;

class StylesheetUIAssetMerger extends UIAssetMerger
{
    /**
     * @var lessc
     */
    private $lessCompiler;

    /**
     * @var UIAsset[]
     */
    private $cssAssetsToReplace = array();

    public function __construct($mergedAsset, $assetFetcher, $cacheBuster)
    {
        parent::__construct($mergedAsset, $assetFetcher, $cacheBuster);

        $this->lessCompiler = self::getLessCompiler();
    }

    protected function getMergedAssets()
    {
        // note: we're using setImportDir on purpose (not addImportDir)
        $this->lessCompiler->setImportDir(PIWIK_DOCUMENT_ROOT);
        $concatenatedAssets = $this->getConcatenatedAssets();

        $this->lessCompiler->setFormatter('classic');
        try {
            $compiled = $this->lessCompiler->compile($concatenatedAssets);
        } catch(\Exception $e) {
            throw new StylesheetLessCompileException($e->getMessage());
        }

        foreach ($this->cssAssetsToReplace as $asset) {
            // to fix #10173
            $cssPath = $asset->getAbsoluteLocation();
            $cssContent = $this->processFileContent($asset);
            $compiled = str_replace($this->getCssStatementForReplacement($cssPath), $cssContent, $compiled);
        }

        $this->mergedContent = $compiled;
        $this->cssAssetsToReplace = array();

        return $compiled;
    }
    
    private function getCssStatementForReplacement($path)
    {
        return '.nonExistingSelectorOnlyForReplacementOfCssFiles { display:"' . $path . '"; }';
    }

    protected function concatenateAssets()
    {
        $mergedContent = '';

        foreach ($this->getAssetCatalog()->getAssets() as $uiAsset) {
            $uiAsset->validateFile();

            try {
                $path = $uiAsset->getAbsoluteLocation();
            } catch (Exception $e) {
                $path = null;
            }

            if (!empty($path) && Common::stringEndsWith($path, '.css')) {
                // to fix #10173
                $mergedContent .= "\n" . $this->getCssStatementForReplacement($path) . "\n";
                $this->cssAssetsToReplace[] = $uiAsset;
            } else {
                $content = $this->processFileContent($uiAsset);
                $mergedContent .= $this->getFileSeparator() . $content;
            }
        }

        $this->mergedContent = $mergedContent;
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
        . "/* Matomo CSS file is compiled with Less. You may be interested in writing a custom Theme for Matomo! */\n";
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
        $pathsRewriter = $this->getCssPathsRewriter($uiAsset);
        $content = $uiAsset->getContent();
        $content = $this->rewriteCssImagePaths($content, $pathsRewriter);
        $content = $this->rewriteCssImportPaths($content, $pathsRewriter);
        return $content;
    }

    /**
     * Rewrite CSS url() directives
     *
     * @param string $content
     * @param callable $pathsRewriter
     * @return string
     */
    private function rewriteCssImagePaths($content, $pathsRewriter)
    {
        $content = preg_replace_callback("/(url\(['\"]?)([^'\")]*)/", $pathsRewriter, $content);
        return $content;
    }

    /**
     * Rewrite CSS import directives
     *
     * @param string $content
     * @param callable $pathsRewriter
     * @return string
     */
    private function rewriteCssImportPaths($content, $pathsRewriter)
    {
        $content = preg_replace_callback("/(@import \")([^\")]*)/", $pathsRewriter, $content);
        return $content;
    }

    /**
     * Rewrite CSS url directives
     * - rewrites paths defined relatively to their css/less definition file
     * - rewrite windows directory separator \\ to /
     *
     * @param UIAsset $uiAsset
     * @return \Closure
     */
    private function getCssPathsRewriter($uiAsset)
    {
        $baseDirectory = dirname($uiAsset->getRelativeLocation());

        return function ($matches) use ($baseDirectory) {
            $absolutePath = PIWIK_DOCUMENT_ROOT . "/$baseDirectory/" . $matches[2];

            // Allow to import extension less file
            if (strpos($matches[2], '.') === false) {
                $absolutePath .= '.less';
            }

            // Prevent from rewriting full path
            $absolutePath = realpath($absolutePath);
            if ($absolutePath) {
                $relativePath = $baseDirectory . "/" . $matches[2];
                $relativePath = str_replace('\\', '/', $relativePath);
                $publicPath   = $matches[1] . $relativePath;
            } else {
                $publicPath   = $matches[1] . $matches[2];
            }

            return $publicPath;
        };
    }

    /**
     * @param UIAsset $uiAsset
     * @return int
     */
    protected function countDirectoriesInPathToRoot($uiAsset)
    {
        $rootDirectory = realpath($uiAsset->getBaseDirectory());

        if ($rootDirectory != PATH_SEPARATOR
            && substr($rootDirectory, -strlen(PATH_SEPARATOR)) !== PATH_SEPARATOR) {
            $rootDirectory .= PATH_SEPARATOR;
        }
        $rootDirectoryLen = strlen($rootDirectory);
        return $rootDirectoryLen;
    }
}
