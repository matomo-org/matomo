<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Proxy;

use Piwik\AssetManager;
use Piwik\AssetManager\UIAsset;
use Piwik\Exception\StylesheetLessCompileException;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Plugin\Manager;
use Piwik\ProxyHttp;
use Piwik\Request;

/**
 * Controller for proxy services
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public const JS_MIME_TYPE = 'application/javascript; charset=UTF-8';

    /**
     * Output the merged CSS file.
     * This method is called when the asset manager is enabled.
     *
     * @return void
     * @see core/AssetManager.php
     */
    public function getCss()
    {
        try {
            $cssMergedFile = AssetManager::getInstance()->getMergedStylesheet();
        } catch (StylesheetLessCompileException $exception) {
            $cssMergedFile = AssetManager::getInstance()->getMergedStylesheet();
        }
        ProxyHttp::serverStaticFile($cssMergedFile->getAbsoluteLocation(), 'text/css');
    }

    /**
     * Output the merged core JavaScript file.
     * This method is called when the asset manager is enabled.
     *
     * @return void
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
     * @return void
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
     * @return void
     * @see core/AssetManager.php
     */
    public function getUmdJs()
    {
        $chunk = Request::fromRequest()->getStringParameter('chunk', '');
        if ($chunk === '') {
            throw new ThingNotFoundException('Invalid chunk requested');
        }
        $chunkFile = AssetManager::getInstance()->getMergedJavaScriptChunk($chunk);
        $this->serveJsFile($chunkFile);
    }

    /**
     * Output a single plugin's UMD JavaScript file.
     * This method is called when the asset manager is enabled and when a plugin's UMD is set
     * to be loaded on demand.
     *
     * @return void
     */
    public function getPluginUmdJs()
    {
        $plugin = Request::fromRequest()->getStringParameter('plugin', '');
        $pluginUmdPath = '';
        if (Manager::getInstance()->isValidPluginName($plugin)) {
            $pluginUmdPath = Manager::getPluginDirectory($plugin) . "/vue/dist/$plugin.umd.min.js";
        }
        ProxyHttp::serverStaticFile($pluginUmdPath, self::JS_MIME_TYPE);
    }

    private function serveJsFile(UIAsset $uiAsset): void
    {
        ProxyHttp::serverStaticFile($uiAsset->getAbsoluteLocation(), self::JS_MIME_TYPE);
    }
}
