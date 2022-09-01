<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue;

use Piwik\Development;

class CoreVue extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
        ];
    }

    public function getJsFiles(&$jsFiles)
    {
        self::addJsFilesTo($jsFiles);
    }

    public static function addJsFilesTo(&$jsFiles)
    {
        $jsFiles[] = Development::isEnabled() ? "node_modules/vue/dist/vue.global.js" : "node_modules/vue/dist/vue.global.prod.js";
        $jsFiles[] = Development::isEnabled() ? "plugins/CoreVue/polyfills/dist/MatomoPolyfills.js" : "plugins/CoreVue/polyfills/dist/MatomoPolyfills.min.js";
    }
}
