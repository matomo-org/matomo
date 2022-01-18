<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Overlay;

class Overlay extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Platform.initialized'                   => 'handleIframeMissingCookie'
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

    /**
     * The Overlay.notifyParentIframe call is sometimes made without a session cookie due to an iframe related race
     * condition, this results in a new session being created which logs the current user out.
     * As a hacky workaround we detect this condition here and remove the set cookie header from the response to
     * protect the session
     */
    public function handleIframeMissingCookie()
    {
        if ($_GET['action'] == 'notifyParentIframe' && count($_COOKIE) == 0) {
            header_remove('Set-Cookie');
        }
    }
}
