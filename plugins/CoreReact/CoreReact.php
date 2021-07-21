<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreReact;

class CoreReact extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
        ];
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        // TODO
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/CoreReact/react/build/main.js';
    }
}
