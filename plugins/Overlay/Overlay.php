<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Overlay;

class Overlay extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    /**
     * Returns required Js Files
     * @param $jsFiles
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Overlay/javascripts/rowaction.js';
        $jsFiles[] = 'plugins/Overlay/javascripts/Overlay_Helper.js';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_OverlayRowActionTooltipTitle';
        $translationKeys[] = 'General_OverlayRowActionTooltip';
    }
}
