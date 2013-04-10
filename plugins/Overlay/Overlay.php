<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Overlay
 */

class Piwik_Overlay extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Overlay_PluginDescription') . ' Note: Requires the Transitions plugin enabled.',
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles' => 'getJsFiles'
        );
    }

    public function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = 'plugins/Overlay/templates/rowaction.js';
        $jsFiles[] = 'plugins/Overlay/templates/helper.js';
    }

}
