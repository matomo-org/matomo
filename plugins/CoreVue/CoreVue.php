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
        $jsFiles[] = Development::isEnabled() ? "plugins/CoreVue/polyfills/dist/MatomoPolyfills.js" : "plugins/CoreVue/polyfills/dist/MatomoPolyfills.min.js";
        $jsFiles[] = Development::isEnabled() ? "node_modules/vue/dist/vue.global.js" : "node_modules/vue/dist/vue.global.prod.js";
        $jsFiles[] = Development::isEnabled() ? "node_modules/vue-class-component/dist/vue-class-component.global.js" : "node_modules/vue-class-component/dist/vue-class-component.global.prod.js";
    }
}
