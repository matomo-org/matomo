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
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getDeferJavaScriptFiles' => 'getDeferJsFiles',
        ];
    }

    public function getJavaScriptFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/rxjs/bundles/rxjs.umd.js";
        $jsFiles[] = "node_modules/@angular/core/bundles/core.umd.js";
        $jsFiles[] = "node_modules/@angular/compiler/bundles/compiler.umd.js";
        $jsFiles[] = "node_modules/@angular/upgrade/bundles/upgrade-static.umd.js";
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
