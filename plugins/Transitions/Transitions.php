<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Transitions
 */

/**
 * @package Piwik_Transitions
 */
class Piwik_Transitions extends Piwik_Plugin
{

    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Transitions_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles'
        );
    }

    public function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();
        $cssFiles[] = 'plugins/Transitions/templates/transitions.css';
    }

    public function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = 'plugins/Transitions/templates/transitions.js';
    }


}