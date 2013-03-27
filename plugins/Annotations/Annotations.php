<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Annotations
 */

/**
 * Annotations plugins. Provides the ability to attach text notes to
 * dates for each sites. Notes can be viewed, modified, deleted or starred.
 *
 * @package Piwik_Annotations
 */
class Piwik_Annotations extends Piwik_Plugin
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Annotations_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * Returns list of event hooks.
     *
     * @return array
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles'
        );
    }

    /**
     * Adds css files for this plugin to the list in the event notification.
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();
        $cssFiles[] = "plugins/Annotations/templates/styles.css";
    }

    /**
     * Adds js files for this plugin to the list in the event notification.
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = "plugins/Annotations/templates/annotations.js";
    }
}
