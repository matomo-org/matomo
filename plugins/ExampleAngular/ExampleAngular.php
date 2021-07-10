<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleAngular;

class ExampleAngular extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'AssetManager.getDeferJavaScriptFiles' => 'getDeferJsFiles',
        ];
    }

    public function getDeferJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/ExampleAngular/angular/dist/bundles/matomo-example-angular.umd.js';
    }
}
