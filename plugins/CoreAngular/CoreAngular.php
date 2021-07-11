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
            'AssetManager.getDeferJavaScriptFiles' => 'getDeferJsFiles',
        ];
    }

    public function getJavaScriptFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/zone.js/bundles/zone.umd.js";
        $jsFiles[] = "node_modules/rxjs/bundles/rxjs.umd.js";
        $jsFiles[] = "node_modules/@angular/core/bundles/core.umd.js";
        $jsFiles[] = "node_modules/@angular/common/bundles/common.umd.js";
        $jsFiles[] = "node_modules/@angular/compiler/bundles/compiler.umd.js";
        $jsFiles[] = "node_modules/@angular/upgrade/bundles/upgrade-static.umd.js";
        $jsFiles[] = "node_modules/@angular/platform-browser/bundles/platform-browser.umd.js";
        $jsFiles[] = "node_modules/@angular/platform-browser-dynamic/bundles/platform-browser-dynamic.umd.js";
    }

    public function getDeferJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/CoreAngular/angular/dist/bundles/matomo-core-angular.umd.js';
    }
}
