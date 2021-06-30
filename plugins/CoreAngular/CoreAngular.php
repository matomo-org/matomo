<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAngular;

class CoreAngular extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getDeferJavaScriptFiles' => 'getDeferJsFiles',
        ];
    }

    public function getDeferJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/CoreAngular/angular/dist/runtime.js';
        $jsFiles[] = 'plugins/CoreAngular/angular/dist/polyfills.js';
        $jsFiles[] = 'plugins/CoreAngular/angular/dist/main.js';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CoreAngular/angular/dist/styles.css";
    }
}
