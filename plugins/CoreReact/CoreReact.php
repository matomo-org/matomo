<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreReact;

use Piwik\Development;

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
        $jsFiles[] = 'node_modules/core-js-bundle/index.js'; // TODO: not sure if needed, but just in case
        $jsFiles[] = 'node_modules/react/umd/react.development.js';
        $jsFiles[] = 'node_modules/react-dom/umd/react-dom.development.js';

        if (Development::isEnabled()) {
            $jsFiles[] = 'node_modules/react-error-overlay/lib/index.js';
        }

        $jsFiles[] = 'plugins/CoreReact/react/build/main.js';
    }
}
