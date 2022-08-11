<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Proxy;

use Piwik\AssetManager;
use Piwik\AssetManager\UIAsset;
use Piwik\Common;
use Piwik\Exception\StylesheetLessCompileException;
use Piwik\ProxyHttp;

/**
 * Controller for proxy services
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    const JS_MIME_TYPE = "application/javascript; charset=UTF-8";

    /**
     * Output the merged CSS file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getCss()
    {
        try {
            $cssMergedFile = AssetManager::getInstance()->getMergedStylesheet();
        } catch (StylesheetLessCompileException $exception) {
            $cssMergedFile = AssetManager::getInstance()->getMergedStylesheet();
        }
        ProxyHttp::serverStaticFile($cssMergedFile->getAbsoluteLocation(), "text/css");
    }

    /**
     * Output the merged core JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getCoreJs()
    {
        $jsMergedFile = AssetManager::getInstance()->getMergedCoreJavaScript();
        $this->serveJsFile($jsMergedFile);
    }

    /**
     * Output the merged non core JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getNonCoreJs()
    {
        $jsMergedFile = AssetManager::getInstance()->getMergedNonCoreJavaScript();
        $this->serveJsFile($jsMergedFile);
    }

    /**
     * Output a UMD merged chunk JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @see core/AssetManager.php
     */
    public function getUmdJs()
    {
        $chunk = Common::getRequestVar('chunk');
        $chunkFile = AssetManager::getInstance()->getMergedJavaScriptChunk($chunk);
        $this->serveJsFile($chunkFile);
    }

    /**
     * @param UIAsset $uiAsset
     */
    private function serveJsFile($uiAsset)
    {
        ProxyHttp::serverStaticFile($uiAsset->getAbsoluteLocation(), self::JS_MIME_TYPE);
    }
}
